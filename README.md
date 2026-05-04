# Online Doctor Channelling System

![CI/CD](https://github.com/thilinagamage/doctor-channelling-system/actions/workflows/ci.yml/badge.svg)

A web-based appointment booking system for private medical channelling centres in Sri Lanka. Built with PHP, MySQL, HTML, CSS, and JavaScript.

---

## Features

- Patient registration and appointment booking
- Doctor browsing by specialization
- Admin panel for managing doctors, slots, and appointments
- Payment recording

## Run with Docker

```bash
git clone https://github.com/thilinagamage/doctor-channelling-system.git
cd doctor-channelling-system
docker compose up --build
```

Visit `http://localhost:8080`

| Role | Email | Password |
|---|---|---|
| Admin | admin@channelling.com | password123 |
| Patient | john@example.com | password123 |

---

## DevOps Pipeline

| Stage | Tool | What it does |
|---|---|---|
| 1 — Containerization | Docker + Compose | Runs PHP, MySQL, Nginx as containers |
| 2 — CI/CD | GitHub Actions | Lint → Build → Push to Docker Hub on every push |
| 3 — Infrastructure | Terraform + Ansible | Provisions AWS EC2, configures server automatically |
| 4 — Orchestration | Kubernetes + Helm | Deploys with auto-scaling (HPA 2→10 pods) |
| 5 — Monitoring | Prometheus + Grafana + Loki | Metrics, dashboards, and log aggregation |

---

## Stack

`PHP 8.1` `MySQL 8.0` `Nginx` `Docker` `GitHub Actions` `Terraform` `Ansible` `AWS EC2` `Kubernetes` `Helm` `Prometheus` `Grafana` `Loki`

---

**Author:** M.A.D.P.D. Mellawa Arachchi — DIT1025090
