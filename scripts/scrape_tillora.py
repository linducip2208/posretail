import os, re, urllib.request, urllib.parse, sys

BASE = "https://rxdtheme.com/html/wrap/pos-retail-dashboard/"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "template-tillora")
OUT = os.path.abspath(OUT)
os.makedirs(OUT, exist_ok=True)

headers = {"User-Agent": "Mozilla/5.0"}

def fetch(url):
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read(), r.headers.get_content_type()

def save(rel_path, data):
    # clean rel_path
    rel_path = rel_path.split("?")[0].split("#")[0]
    if not rel_path or rel_path.endswith("/"):
        rel_path = rel_path + "index.html" if rel_path else "index.html"
    full = os.path.join(OUT, rel_path.replace("/", os.sep))
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "wb") as f:
        f.write(data)
    return full

visited_html = set()
visited_asset = set()
queue = ["index.html"]

# regex patterns
attr_pat = re.compile(r'''(?:href|src|data-src)\s*=\s*["']([^"']+)["']''', re.I)
css_url_pat = re.compile(r'''url\(\s*["']?([^"'\)]+)["']?\s*\)''', re.I)

def is_external(u):
    return u.startswith("http://") or u.startswith("https://") or u.startswith("//") or u.startswith("data:") or u.startswith("mailto:") or u.startswith("tel:") or u.startswith("#")

count_html = 0
count_asset = 0

while queue:
    rel = queue.pop(0)
    if rel in visited_html:
        continue
    visited_html.add(rel)
    url = urllib.parse.urljoin(BASE, rel)
    print(f"[HTML] {url}")
    try:
        data, ctype = fetch(url)
    except Exception as e:
        print(f"  FAIL: {e}")
        continue
    save(rel if rel else "index.html", data)
    count_html += 1
    try:
        text = data.decode("utf-8", errors="ignore")
    except:
        continue
    links = attr_pat.findall(text)
    for link in links:
        if not link or is_external(link):
            continue
        if link.startswith("javascript:"):
            continue
        # normalize
        clean = link.split("?")[0].split("#")[0]
        if not clean:
            continue
        if clean.endswith(".html") or ("." not in os.path.basename(clean) and not clean.startswith("assets/")):
            # page link, could be documentation/index.html style
            target = clean if clean.endswith(".html") else clean
            # resolve relative to current page dir? pages are flat, but handle
            base_dir = os.path.dirname(rel)
            resolved = os.path.normpath(os.path.join(base_dir, target)).replace("\\", "/") if base_dir else target
            if resolved.startswith("../"):
                resolved = os.path.basename(resolved)
            if resolved not in visited_html and not resolved.startswith(".."):
                queue.append(resolved)
        elif clean.startswith("assets/") or clean.startswith("documentation/"):
            if clean not in visited_asset:
                try:
                    aurl = urllib.parse.urljoin(BASE, clean)
                    print(f"  [ASSET] {aurl}")
                    adata, _ = fetch(aurl)
                    save(clean, adata)
                    visited_asset.add(clean)
                    count_asset += 1
                    # if css, parse inner urls
                    if clean.endswith(".css"):
                        try:
                            csstext = adata.decode("utf-8", errors="ignore")
                            for m in css_url_pat.findall(csstext):
                                if is_external(m) or m.startswith("data:"):
                                    continue
                                css_dir = os.path.dirname(clean)
                                r2 = os.path.normpath(os.path.join(css_dir, m)).replace("\\", "/")
                                if r2 not in visited_asset and r2.startswith("assets/"):
                                    try:
                                        u2 = urllib.parse.urljoin(BASE, r2)
                                        print(f"    [CSS-DEP] {u2}")
                                        d2, _ = fetch(u2)
                                        save(r2, d2)
                                        visited_asset.add(r2)
                                        count_asset += 1
                                    except Exception as e:
                                        print(f"    FAIL css-dep {r2}: {e}")
                        except Exception as e:
                            print(f"  css parse fail: {e}")
                except Exception as e:
                    print(f"  FAIL asset {clean}: {e}")

print(f"\nDone HTML={count_html} ASSETS={count_asset}")
print(f"OUT={OUT}")

# Second pass: scan saved CSS/JS for missed assets (fonts etc.)
for root, dirs, files in os.walk(OUT):
    for fn in files:
        if fn.endswith(".css"):
            fp = os.path.join(root, fn)
            with open(fp, "r", encoding="utf-8", errors="ignore") as f:
                csstext = f.read()
            for m in css_url_pat.findall(csstext):
                if is_external(m) or m.startswith("data:"):
                    continue
                rel_css = os.path.relpath(fp, OUT).replace("\\", "/")
                css_dir = os.path.dirname(rel_css)
                r2 = os.path.normpath(os.path.join(css_dir, m)).replace("\\", "/")
                full2 = os.path.join(OUT, r2.replace("/", os.sep))
                if not os.path.exists(full2) and r2.startswith("assets/"):
                    try:
                        u2 = urllib.parse.urljoin(BASE, r2)
                        print(f"[MISS-CSS] {u2}")
                        d2, _ = fetch(u2)
                        save(r2, d2)
                    except Exception as e:
                        print(f"  FAIL {r2}: {e}")
print("Second pass done.")
