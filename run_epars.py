import subprocess
import socket

PROJECT_PATH = r"C:\xampp\htdocs\denr_opais"

def get_local_ip():
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    try:
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
    except Exception:
        ip = "127.0.0.1"
    finally:
        s.close()
    return ip

IP_ADDRESS = get_local_ip()

# Clear cache first
subprocess.run(
    f'cmd /c "cd /d {PROJECT_PATH} && php artisan optimize:clear"',
    shell=True
)

# Start the servers
commands = [
    "php artisan serve",
    "npm run dev",
    f"php artisan serve --host={IP_ADDRESS} --port=8000"
]

titles = [
    "Laravel Local",
    "Vite Dev Server",
    "Laravel Network"
]

for title, command in zip(titles, commands):
    subprocess.Popen(
        f'start "{title}" cmd /k "cd /d {PROJECT_PATH} && {command}"',
        shell=True
    )

print(f"Network URL: http://{IP_ADDRESS}:8000")