# Online Doctor Channelling System

A web-based doctor appointment booking system built with PHP, MySQL, and JavaScript — containerized and deployed using a full DevOps pipeline.

![CI/CD Pipeline](https://github.com/thilinagamage/doctor-channelling-system/actions/workflows/ci.yml/badge.svg)

---

## What it does

Patients can register, browse doctors by specialization, book appointments, and make payments online. Admin staff manage doctors, time slots, and appointments through a dedicated dashboard.

---

## Tech Stack

**Application:** PHP 8.1, MySQL 8.0, HTML, CSS, JavaScript

**DevOps:** Docker, Docker Compose, GitHub Actions, Terraform, Ansible, Kubernetes, Helm, Prometheus, Grafana, Loki

---

## Run locally with Docker

**Prerequisites:** Docker Desktop installed

```bash
git clone https://github.com/thilinagamage/doctor-channelling-system.git
cd doctor-channelling-system
docker compose up --build
```

Open `http://localhost:8080`

| Account | Email | Password |
|---|---|---|
| Admin | admin@channelling.com | password123
| Patient | test@patient.com | Test@1234 |

---

## DevOps Pipeline

### Stage 1 — Docker
- `Dockerfile` using `php:8.1-apache`
- `docker-compose.yml` with PHP app, MySQL, and Nginx
- Database auto-seeded from `db/init.sql` on first start

### Stage 2 — CI/CD (GitHub Actions)
- Runs on every push to `main`
- PHP syntax check across all files
- Builds and pushes Docker image to Docker Hub
- Tags image with `latest` and commit SHA

### Stage 3 — Infrastructure as Code
- **Terraform** provisions AWS EC2 instance, security group, and SSH key pair
- **Remote state** stored in S3 bucket
- **Ansible** installs Docker on the server and deploys containers automatically

```bash
cd terraform && terraform apply
ansible-playbook -i ansible/inventory.ini ansible/playbook.yml
```

### Stage 4 — Kubernetes
- Deployment with 2 PHP replicas and liveness/readiness probes
- MySQL StatefulSet with PersistentVolume
- HorizontalPodAutoscaler scaling 2 → 10 pods at 70% CPU
- ConfigMap and Secret for environment variables
- Ingress for routing
- Packaged as a Helm chart

```bash
minikube start
helm install channelling ./helm/channelling
minikube service channelling-php-service -n channelling --url
```

### Stage 5 — Monitoring
- **Prometheus** scrapes metrics every 15 seconds
- **Grafana** dashboards for MySQL and container metrics (port 3000)
- **Loki + Promtail** for container log aggregation
- **MySQL exporter** exposing database metrics
- **Alert rules** for MySQL downtime and high error rates

```bash
docker compose up -d
# Prometheus: http://localhost:9090
# Grafana:    http://localhost:3000  (admin / admin123)
```

---

## Project Structure

```
channelling-system/
├── Dockerfile
├── docker-compose.yml
├── nginx/
│   └── default.conf
├── db/
│   └── init.sql
├── config/
│   └── db.php
├── monitoring/
│   ├── prometheus.yml
│   ├── alert_rules.yml
│   └── grafana/
├── terraform/
│   ├── main.tf
│   ├── variables.tf
│   └── outputs.tf
├── ansible/
│   ├── inventory.ini
│   └── playbook.yml
├── k8s/
│   ├── namespace.yml
│   ├── configmap.yml
│   ├── secret.yml
│   ├── mysql-statefulset.yml
│   ├── php-deployment.yml
│   ├── hpa.yml
│   └── ingress.yml
└── helm/
    └── channelling/
```

---

## AWS Deployment

Live on AWS EC2 (ap-south-1) at `http://43.204.98.142`

Provisioned with Terraform. Configured with Ansible.

---

## Author

**Thilina Gamage**


![CI/CD Pipeline](https://github.com/thilinagamage/doctor-channelling-system/actions/workflows/ci.yml/badge.svg)
