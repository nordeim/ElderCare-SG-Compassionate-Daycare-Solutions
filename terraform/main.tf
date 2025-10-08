# ElderCare SG - AWS Infrastructure
# Terraform v1.6+
# Provider: AWS (Singapore region)

terraform {
  required_version = ">= 1.6.0"
  
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  backend "s3" {
    bucket         = "eldercare-sg-terraform-state"
    key            = "staging/terraform.tfstate"
    region         = "ap-southeast-1"
    encrypt        = true
    dynamodb_table = "eldercare-terraform-locks"
  }
}

provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Project     = "ElderCare SG"
      Environment = var.environment
      ManagedBy   = "Terraform"
      Compliance  = "PDPA"
    }
  }
}

# VPC Module
module "vpc" {
  source = "./modules/vpc"
  
  environment = var.environment
  vpc_cidr    = var.vpc_cidr
  
  availability_zones = var.availability_zones
  public_subnets     = var.public_subnets
  private_subnets    = var.private_subnets
  database_subnets   = var.database_subnets
}

# RDS MySQL Module
module "rds" {
  source = "./modules/rds"
  
  environment            = var.environment
  vpc_id                 = module.vpc.vpc_id
  database_subnets       = module.vpc.database_subnet_ids
  
  instance_class         = var.rds_instance_class
  allocated_storage      = var.rds_allocated_storage
  engine_version         = "8.0.35"
  database_name          = "eldercare_db"
  master_username        = var.db_username
  master_password        = var.db_password
  
  backup_retention_period = 7
  multi_az               = var.environment == "production" ? true : false
  deletion_protection    = var.environment == "production" ? true : false
}

# ElastiCache Redis Module
module "redis" {
  source = "./modules/redis"
  
  environment      = var.environment
  vpc_id           = module.vpc.vpc_id
  private_subnets  = module.vpc.private_subnet_ids
  
  node_type        = var.redis_node_type
  num_cache_nodes  = var.environment == "production" ? 2 : 1
  engine_version   = "7.0"
}

# S3 Buckets Module
module "s3" {
  source = "./modules/s3"
  
  environment = var.environment
  
  # Media storage bucket
  media_bucket_name = "eldercare-sg-media-${var.environment}"
  
  # Enable versioning for production
  enable_versioning = var.environment == "production" ? true : false
}

# ECS Cluster Module
module "ecs" {
  source = "./modules/ecs"
  
  environment         = var.environment
  vpc_id              = module.vpc.vpc_id
  public_subnets      = module.vpc.public_subnet_ids
  private_subnets     = module.vpc.private_subnet_ids
  
  # Task definitions
  frontend_image      = var.frontend_image
  backend_image       = var.backend_image
  
  # Environment variables
  app_key             = var.app_key
  database_url        = module.rds.connection_url
  redis_url           = module.redis.connection_url
  
  # Scaling
  frontend_desired_count = var.frontend_desired_count
  backend_desired_count  = var.backend_desired_count
}

# CloudFront CDN Module
module "cloudfront" {
  source = "./modules/cloudfront"
  
  environment       = var.environment
  media_bucket_id   = module.s3.media_bucket_id
  alb_dns_name      = module.ecs.alb_dns_name
  
  domain_name       = var.domain_name
  certificate_arn   = var.acm_certificate_arn
}

# Secrets Manager
resource "aws_secretsmanager_secret" "app_secrets" {
  name        = "eldercare-${var.environment}-secrets"
  description = "Application secrets for ElderCare SG ${var.environment}"
  
  recovery_window_in_days = 7
}

resource "aws_secretsmanager_secret_version" "app_secrets" {
  secret_id = aws_secretsmanager_secret.app_secrets.id
  
  secret_string = jsonencode({
    DB_PASSWORD         = var.db_password
    APP_KEY            = var.app_key
    REDIS_PASSWORD     = ""
    CALENDLY_API_KEY   = var.calendly_api_key
    MAILCHIMP_API_KEY  = var.mailchimp_api_key
    TWILIO_AUTH_TOKEN  = var.twilio_auth_token
    SENTRY_DSN         = var.sentry_dsn
  })
}
