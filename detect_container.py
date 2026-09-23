import os
import platform

def read_file(path):
    try:
        with open(path) as f:
            return f.read()
    except (FileNotFoundError, PermissionError):
        return ""

def check_docker():
    # .dockerenv file at root
    if os.path.exists("/.dockerenv"):
        return True
    # cgroup mentions docker
    cgroup = read_file("/proc/1/cgroup")
    if "docker" in cgroup:
        return True
    # /proc/self/mountinfo contains overlay from docker
    mountinfo = read_file("/proc/self/mountinfo")
    if "docker" in mountinfo:
        return True
    return False

def check_kubernetes():
    # Kubernetes injects these env vars
    if os.environ.get("KUBERNETES_SERVICE_HOST"):
        return True
    if os.environ.get("KUBERNETES_PORT"):
        return True
    # Service account token mounted by kubelet
    if os.path.exists("/var/run/secrets/kubernetes.io/serviceaccount/token"):
        return True
    # cgroup mentions kubepods
    cgroup = read_file("/proc/1/cgroup")
    if "kubepods" in cgroup:
        return True
    return False

def check_lxc():
    # LXC sets this env var
    if os.environ.get("container") == "lxc":
        return True
    # /proc/1/environ may contain container=lxc
    environ = read_file("/proc/1/environ")
    if b"container=lxc" in environ.encode() if isinstance(environ, str) else b"container=lxc" in environ:
        return True
    # cgroup mentions lxc
    cgroup = read_file("/proc/1/cgroup")
    if "lxc" in cgroup:
        return True
    # systemd-detect-virt marks lxc
    if os.path.exists("/run/systemd/container"):
        virt = read_file("/run/systemd/container").strip()
        if virt == "lxc":
            return True
    return False

def detect():
    results = {
        "Docker":     check_docker(),
        "Kubernetes": check_kubernetes(),
        "LXC":        check_lxc(),
    }

    print("=== Container Detection ===")
    print(f"Hostname : {platform.node()}")
    print(f"Kernel   : {platform.release()}\n")

    detected = [name for name, found in results.items() if found]

    for name, found in results.items():
        status = "YES" if found else "NO"
        print(f"{name:<12}: {status}")

    print()
    if detected:
        print(f"Running inside: {', '.join(detected)}")
    else:
        print("Running on bare metal or an unrecognised virtualisation layer.")

detect()
