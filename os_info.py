import platform
import subprocess

def read_file(path):
    try:
        with open(path) as f:
            return f.read().strip()
    except FileNotFoundError:
        return None

print("=== OS Information ===")

# /etc/os-release
os_release = read_file("/etc/os-release")
if os_release:
    print("\n-- /etc/os-release --")
    print(os_release)

# /proc/version
proc_version = read_file("/proc/version")
if proc_version:
    print("\n-- /proc/version --")
    print(proc_version)

# uname
print("\n-- uname --")
u = platform.uname()
print(f"System   : {u.system}")
print(f"Node     : {u.node}")
print(f"Release  : {u.release}")
print(f"Version  : {u.version}")
print(f"Machine  : {u.machine}")
print(f"Processor: {u.processor}")

# lsb_release
print("\n-- lsb_release --")
try:
    out = subprocess.check_output(["lsb_release", "-a"], stderr=subprocess.DEVNULL, text=True)
    print(out.strip())
except FileNotFoundError:
    print("lsb_release not available")
