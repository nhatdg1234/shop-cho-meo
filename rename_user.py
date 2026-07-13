from pathlib import Path
import os

root = Path("shop/User")

items = sorted(
    [p for p in root.rglob("*")],
    key=lambda p: len(p.parts),
    reverse=True,
)

renames = []

for p in items:
    name = p.name
    new_name = name.replace(" ", "-").replace("-", "_")
    if new_name != name:
        target = p.with_name(new_name)
        if target.exists():
            print(f"COLLISION: {p} -> {target}")
        else:
            renames.append((p, target))

for src, dst in renames:
    dst.parent.mkdir(parents=True, exist_ok=True)
    os.rename(src, dst)
    print(f"{src} -> {dst}")

print(f"Renamed {len(renames)} items")