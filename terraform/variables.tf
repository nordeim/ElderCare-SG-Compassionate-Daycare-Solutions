# Environment
variable "environment" {
  description = "Environment name (staging, production)"
  type        = string
}

variable "aws_region" {
  description = "AWS region (Singapore)"
  type        = string
  default     = "ap-southeast-1"
}

# VPC Configuration
variable "vpc_cidr" {
  description = "CIDR block for VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "availability_zones" {
  description = "Availability zones"
  type        = list(string)
  default     = ["ap-southeast-1a", "ap-southeast-1b", "ap-southeast-1c"]
}

variable "public_subnets" {
  description = "CIDR blocks for public subnets"
  type        = list(string)
  default     = ["10.0.1.0/24", "10.0.2.0/24", "10.0.3.0/24"]
}

variable "private_subnets" {
  description = "CIDR blocks for private subnets"
  type        = list(string)
  default     = ["10.0.11.0/24", "10.0.12.0/24", "10.0.13.0/24"]
}

variable "database_subnets" {
  description = "CIDR blocks for database subnets"
  type        = list(string)
  default     = ["10.0.21.0/24", "10.0.22.0/24", "10.0.23.0/24"]
}

# RDS Configuration
variable "rds_instance_class" {
  description = "RDS instance class"
  type        = string
  default     = "db.t3.micro"  # Staging default
}

variable "rds_allocated_storage" {
  description = "RDS allocated storage (GB)"
  type        = number
  default     = 20
}

variable "db_username" {
  description = "Database master username"
  type        = string
  default     = "eldercare_admin"
  sensitive   = true
}

variable "db_password" {
  description = "Database master password"
  type        = string
  sensitive   = true
}

# Redis Configuration
variable "redis_node_type" {
  description = "ElastiCache node type"
  type        = string
  default     = "cache.t3.micro"  # Staging default
}

# ECS Configuration
variable "frontend_image" {
  description = "Frontend Docker image"
  type        = string
}

variable "backend_image" {
  description = "Backend Docker image"
  type        = string
}

variable "frontend_desired_count" {
  description = "Desired count of frontend tasks"
  type        = number
  default     = 1
}

variable "backend_desired_count" {
  description = "Desired count of backend tasks"
  type        = number
  default     = 1
}

# Application Configuration
variable "app_key" {
  description = "Laravel application key"
  type        = string
  sensitive   = true
}

# Domain & SSL
variable "domain_name" {
  description = "Domain name"
  type        = string
}

variable "acm_certificate_arn" {
  description = "ACM certificate ARN"
  type        = string
}

# External Services
variable "calendly_api_key" {
  description = "Calendly API key"
  type        = string
  sensitive   = true
}

variable "mailchimp_api_key" {
  description = "Mailchimp API key"
  type        = string
  sensitive   = true
}

variable "twilio_auth_token" {
  description = "Twilio auth token"
  type        = string
  sensitive   = true
}

variable "sentry_dsn" {
  description = "Sentry DSN"
  type        = string
  sensitive   = true
}
