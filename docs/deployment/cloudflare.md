# Cloudflare Configuration Guide

## Overview
ElderCare SG uses Cloudflare for:
- CDN (static asset caching)
- WAF (Web Application Firewall)
- DDoS protection
- SSL/TLS termination
- DNS management

## Configuration Steps

### 1. DNS Setup
Create the following DNS records for the staging and production environments:

| Type | Name | Value | TTL | Notes |
|------|------|-------|-----|-------|
| A | @ | <ALB_IP> | Auto | Point to load balancer or use CNAME to ALB/CloudFront |
| CNAME | www | frontend-service.example.com | Auto | Use CNAME for frontend service |

### 2. SSL/TLS Configuration
- Mode: Full (strict)
- Use Origin Certificates between Cloudflare and origin
- Enable HSTS if you control domain permanently

### 3. Page Rules
- Cache everything for `/assets/*`
- Edge cache by device type when appropriate
- Always use HTTPS

### 4. Firewall Rules
- Rate limiting on API endpoints (e.g., `/api/v1/*`)
- Challenge on high threat score
- Block or challenge specific countries as policy requires

### 5. Cloudflare Stream (Phase 5/6)
- Use Cloudflare Stream for video hosting and adaptive streaming
- Configure upload tokens and signed playback URLs

## Terraform Integration
- Add a Cloudflare module to manage DNS records and WAF rules
- Example: use `cloudflare_record`, `cloudflare_zone_settings_override`, `cloudflare_firewall_rule`

## Troubleshooting
- If assets are not updating, purge cache for the path
- If health checks fail, confirm origin IPs and firewall rules
