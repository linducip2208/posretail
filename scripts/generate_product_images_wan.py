#!/usr/bin/env python3
"""Generate foto produk POS Retail via Wan 2.1 T2V 1.3B (single-frame still, packshot putih).
Usage:
  python scripts/generate_product_images_wan.py            # generate semua yg belum ada
  python scripts/generate_product_images_wan.py --limit 1 # test 1 dulu
  python scripts/generate_product_images_wan.py --force   # regenerate semua
Resumable: skip file yg sudah ada di storage/app/public/products/<slug>.jpg
"""
import argparse, hashlib, json, os, sys, time, uuid, urllib.request, subprocess

COMFY = os.environ.get("COMFYUI_URL", "http://127.0.0.1:8188")
DIFFUSION = "wan2.1_t2v_1.3B_fp16.safetensors"
T5 = "umt5-xxl-enc-fp8_e4m3fn.safetensors"
VAE = "wan_2.1_vae.safetensors"
W, H, FRAMES, STEPS, CFG, SHIFT = 768, 768, 1, 10, 6.0, 5.0
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUTDIR = os.path.join(ROOT, "storage", "app", "public", "products")
PROGRESS = os.path.join(ROOT, "storage", "app", "public", "products", "_progress_wan.json")

CAT_EN = {
    1: "snack bag, chips and biscuits sealed packaging",
    2: "beverage bottle, tea soda juice drink packaging",
    3: "instant noodles pack and cooking seasoning packaging",
    4: "soap detergent cleaning product bottle packaging",
    5: "stationery product, notebook pen pencil",
    6: "small electronic accessory in retail box packaging",
    7: "cigarette pack, tobacco box packaging",
    8: "staple food packaging, rice sugar cooking oil",
    9: "milk and dairy carton bottle packaging",
    10: "bread cookies cereal box packaging",
    11: "frozen food pouch packaging, nugget sausage",
    12: "canned food tin can packaging",
    13: "sauce and ketchup squeeze bottle packaging",
    14: "cooking oil pouch and margarine tub packaging",
    15: "rice and grains sack pouch packaging",
    16: "coffee tea chocolate sachet box packaging",
    17: "candy and chocolate bar wrapper packaging",
    18: "baby care product, diapers formula box packaging",
    19: "body care lotion bottle tube packaging",
    20: "shampoo hair care bottle packaging",
    21: "medicine and vitamin box strip packaging",
    22: "pet food bag packaging",
    23: "kids toy in retail box",
    24: "household tool product",
    25: "retail bundle promo package",
}

NEG = ("blurry, deformed, broken, melted, multiple products, hands, people, "
       "animal, dark background, wooden table, outdoor, watermark, text overlay, "
       "low quality, distorted, cropped, heavy shadow, moving, motion blur, video artifacts")

def build_prompt(name: str, cat_id: int, cat_name: str) -> str:
    desc = CAT_EN.get(cat_id, "sealed retail product packaging")
    return (f"static product photography of {name}, {desc}, "
            f"Indonesian supermarket product, sealed retail packaging, centered front view, "
            f"full product visible, single product only, perfectly still, sharp studio photograph, "
            f"on pure white background, soft studio softbox lighting, subtle soft shadow under product, "
            f"ultra sharp focus, commercial catalog photography, high detail")

def wan_workflow(prompt: str, seed: int) -> dict:
    return {
        "1": {"class_type": "WanVideoModelLoader", "inputs": {
            "model": DIFFUSION, "base_precision": "fp16",
            "quantization": "disabled", "load_device": "offload_device"}},
        "2": {"class_type": "LoadWanVideoT5TextEncoder", "inputs": {
            "model_name": T5, "precision": "bf16"}},
        "3": {"class_type": "WanVideoVAELoader", "inputs": {"model_name": VAE, "precision": "bf16"}},
        "4": {"class_type": "WanVideoTextEncode", "inputs": {
            "positive_prompt": prompt, "negative_prompt": NEG, "t5": ["2", 0]}},
        "5": {"class_type": "WanVideoEmptyEmbeds", "inputs": {
            "width": W, "height": H, "num_frames": FRAMES}},
        "6": {"class_type": "WanVideoSampler", "inputs": {
            "model": ["1", 0], "image_embeds": ["5", 0], "text_embeds": ["4", 0],
            "steps": STEPS, "cfg": CFG, "shift": SHIFT, "seed": seed,
            "force_offload": True, "scheduler": "unipc", "riflex_freq_index": 0}},
        "7": {"class_type": "WanVideoDecode", "inputs": {
            "vae": ["3", 0], "samples": ["6", 0],
            "enable_vae_tiling": False, "tile_x": 272, "tile_y": 272,
            "tile_stride_x": 144, "tile_stride_y": 128}},
        "8": {"class_type": "SaveImage", "inputs": {
            "images": ["7", 0], "filename_prefix": "pos-retail/wan-product"}},
    }

def comfy_generate(prompt: str, seed: int) -> dict:
    wf = wan_workflow(prompt, seed)
    cid = str(uuid.uuid4())
    data = json.dumps({"prompt": wf, "client_id": cid}).encode()
    req = urllib.request.Request(f"{COMFY}/prompt", data=data, headers={"Content-Type": "application/json"})
    r = json.loads(urllib.request.urlopen(req, timeout=15).read())
    pid = r["prompt_id"]
    for _ in range(120):
        time.sleep(5)
        h = json.loads(urllib.request.urlopen(f"{COMFY}/history/{pid}", timeout=15).read())
        if pid in h:
            node_hist = h[pid]
            if node_hist.get("status", {}).get("completed") is False or "error" in node_hist.get("status", {}):
                raise RuntimeError(f"ComfyUI error: {node_hist['status']}")
            if node_hist.get("outputs"):
                out = node_hist["outputs"]["8"]["images"][0]
                return out
    raise RuntimeError(f"timeout prompt {pid}")

def download_image(info: dict) -> bytes:
    q = f"filename={info['filename']}&subfolder={info.get('subfolder','')}&type={info.get('type','output')}"
    return urllib.request.urlopen(f"{COMFY}/view?{q}", timeout=30).read()

def export_products() -> list:
    php = ("echo json_encode(App\\Models\\Product::with('category:id,name')"
           "->get(['id','name','slug','category_id'])->toArray());")
    out = subprocess.check_output(["php", "artisan", "tinker", "--execute", php],
                                  cwd=ROOT, timeout=60).decode("utf-8", errors="ignore")
    s = out[out.rfind("["):]
    return json.loads(s)

def seed_for(slug: str, pid: int) -> int:
    h = hashlib.md5(slug.encode()).hexdigest()[:8]
    return (int(h, 16) + pid * 7919) % (2**31 - 1)

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--limit", type=int, default=0)
    ap.add_argument("--force", action="store_true")
    ap.add_argument("--category", type=int, default=0)
    args = ap.parse_args()

    os.makedirs(OUTDIR, exist_ok=True)
    try:
        from PIL import Image
        import io
        has_pil = True
    except ImportError:
        has_pil = False

    products = export_products()
    if args.category:
        products = [p for p in products if p["category_id"] == args.category]
    if args.limit:
        missing = [p for p in products if not os.path.exists(os.path.join(OUTDIR, f"{p['slug']}.jpg"))]
        rest = [p for p in products if p not in missing]
        products = (missing + rest)[:args.limit]
    print(f"Total antrian: {len(products)} produk -> {OUTDIR} (Wan 1-frame {W}x{H}, {STEPS} steps)")

    done, fail, skip = 0, 0, 0
    t0 = time.time()
    for i, p in enumerate(products, 1):
        slug, name = p["slug"], p["name"]
        cat_id = p["category_id"]
        cat_name = (p.get("category") or {}).get("name", "")
        dest = os.path.join(OUTDIR, f"{slug}.jpg")
        if os.path.exists(dest) and not args.force:
            skip += 1
            continue
        prompt = build_prompt(name, cat_id, cat_name)
        try:
            info = comfy_generate(prompt, seed_for(slug, p["id"]))
            raw = download_image(info)
            if has_pil:
                im = Image.open(io.BytesIO(raw)).convert("RGB")
                im.save(dest, "JPEG", quality=88)
            else:
                open(dest, "wb").write(raw)
            done += 1
            el = time.time() - t0
            print(f"[{i}/{len(products)}] OK {slug} ({el/max(done,1):.1f}s/gbr, {el/60:.1f} mnt total)", flush=True)
        except Exception as e:
            fail += 1
            print(f"[{i}/{len(products)}] FAIL {slug}: {e}", flush=True)
        try:
            json.dump({"done": done, "fail": fail, "skip": skip, "total": len(products)},
                      open(PROGRESS, "w"))
        except Exception:
            pass
    print(f"Selesai: ok={done} skip={skip} fail={fail} waktu={(time.time()-t0)/60:.1f} mnt")

if __name__ == "__main__":
    main()
