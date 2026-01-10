import shutil
import os

src = r"C:\laragon\www\hr_budget\research\wireframe_disbursement_form_v3.html"
dst = r"C:\laragon\www\hr_budget\research\wireframe_hr_request_v1.html"

print(f"Copying form {src} to {dst}")
try:
    shutil.copy2(src, dst)
    print("Copy success")
except Exception as e:
    print(f"Error: {e}")
