# Queue Worker Ops Guide (Supervisor / systemd)

## 1) Supervisor (Ubuntu/Debian)

1. Install supervisor:
```bash
sudo apt-get update && sudo apt-get install -y supervisor
```

2. Buat file program supervisor:
```bash
sudo tee /etc/supervisor/conf.d/kampuspay-queue.conf >/dev/null <<'CONF'
[program:kampuspay-queue]
command=php /var/www/kampuspay/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/kampuspay
redirect_stderr=true
autostart=true
autorestart=true
numprocs=1
user=www-data
stdout_logfile=/var/log/supervisor/kampuspay-queue.log
stopwaitsecs=15
killasgroup=true
stopsignal=QUIT
CONF
```

3. Reload & start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start kampuspay-queue
```

4. Cek status:
```bash
sudo supervisorctl status kampuspay-queue
```

## 2) systemd (alternatif)

1. Buat service file:
```bash
sudo tee /etc/systemd/system/kampuspay-queue.service >/dev/null <<'UNIT'
[Unit]
Description=KampusPay Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/kampuspay
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5
KillSignal=SIGQUIT
TimeoutStopSec=15

[Install]
WantedBy=multi-user.target
UNIT
```

2. Reload & enable & start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable kampuspay-queue
sudo systemctl start kampuspay-queue
```

3. Logs:
```bash
journalctl -u kampuspay-queue -f
```

## 3) Rekomendasi
- Gunakan `--tries=3` dan monitoring error (Sentry dsb.)
- Pastikan `.env` QUEUE_CONNECTION=database/redis sesuai
- Setup healthcheck sederhana (cek proses hidup setiap 1 menit)
- Backup rutin dan uji restore


