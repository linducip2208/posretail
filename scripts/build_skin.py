import pathlib
OUT = pathlib.Path("public/app-assets/css/app.css")
files = sorted(pathlib.Path("public/app-assets/css/skin").glob("*.css"), key=lambda p: p.name)
parts = []
for f in files:
    t = f.read_text(encoding="utf-8")
    assert t.count("{") == t.count("}"), f"unbalanced {f.name}"
    parts.append(f"/* ===== {f.name} ===== */\n" + t)
OUT.write_text("\n".join(parts), encoding="utf-8")
import os
print("concatenated", len(files), "files,", os.path.getsize(str(OUT)), "bytes")
