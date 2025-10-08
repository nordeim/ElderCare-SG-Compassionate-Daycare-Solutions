# VPC Outputs
output "vpc_id" {
  description = "VPC ID"
  value       = module.vpc.vpc_id
}

output "public_subnet_ids" {
  description = "Public subnet IDs"
  value       = module.vpc.public_subnet_ids
}

# RDS Outputs
output "rds_endpoint" {
  description = "RDS endpoint"
  value       = module.rds.endpoint
  sensitive   = true
}

output "rds_connection_url" {
  description = "RDS connection URL"
  value       = module.rds.connection_url
  sensitive   = true
}

# Redis Outputs
output "redis_endpoint" {
  description = "Redis endpoint"
  value       = module.redis.endpoint
}

# S3 Outputs
output "media_bucket_name" {
  description = "Media S3 bucket name"
  value       = module.s3.media_bucket_name
}

output "media_bucket_domain" {
  description = "Media S3 bucket domain"
  value       = module.s3.media_bucket_domain
}

# ECS Outputs
output "ecs_cluster_name" {
  description = "ECS cluster name"
  value       = module.ecs.cluster_name
}

output "alb_dns_name" {
  description = "Application Load Balancer DNS name"
  value       = module.ecs.alb_dns_name
}

# CloudFront Outputs
output "cloudfront_domain_name" {
  description = "CloudFront distribution domain"
  value       = module.cloudfront.domain_name
}

# Secrets
output "secrets_manager_arn" {
  description = "Secrets Manager ARN"
  value       = aws_secretsmanager_secret.app_secrets.arn
}
