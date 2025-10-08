# Terraform Infrastructure Overview

## Module Structure
- **Root configuration** (`main.tf`, `variables.tf`, `outputs.tf`): Defines AWS VPC, ECS services, RDS, ElastiCache, S3, IAM roles, and networking baselines for the ElderCare SG platform.
- **Environment tfvars** (`staging.tfvars`): Provides staged parameter values (CIDR blocks, instance sizes, desired counts) for the staging environment. Add `production.tfvars` before production rollout.

```
terraform/
├── main.tf              # Core module invocations
├── variables.tf         # Input variable definitions and defaults
├── outputs.tf           # Outputs exported to CI/CD pipelines
├── staging.tfvars       # Environment-specific values (staging)
└── README.md            # You are here
```

## Provisioning Workflow
1. Install Terraform >= 1.5.0.
2. Authenticate with AWS (profile or environment variables).
3. Initialize modules:
   ```bash
   terraform init
   ```
4. Review plan for target environment (example: staging):
   ```bash
   terraform plan -var-file=staging.tfvars
   ```
5. Apply when approved:
   ```bash
   terraform apply -var-file=staging.tfvars
   ```

## Cloudflare Integration
- Cloudflare CDN/WAF is currently documented only; Terraform integration pending.
- Action item: create module (or reuse registry module) to manage DNS records, SSL settings, and WAF rules for `staging.eldercare.sg` and production domain.
- Track Terraform issue to incorporate Cloudflare resources and outputs consumed by frontend/backend deployments.

## Outputs
The root module exposes key values to CI/CD pipelines:
- `alb_dns_name`
- `backend_service_url`
- `frontend_service_url`
- `rds_endpoint`
- `redis_endpoint`
- `s3_media_bucket`

Consume these via GitHub Actions environment variables or Secrets Manager for deployment stages.

## Next Steps
- Validate existing configuration with `terraform validate`.
- Expand tfvars for production environment.
- Document secret management workflow (AWS Secrets Manager / SSM) to supply Laravel and Next.js runtime configuration.
- Once Cloudflare module is added, update this README and the deployment documentation.
