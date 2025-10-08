# Staging Environment Configuration

environment = "staging"
aws_region  = "ap-southeast-1"

# VPC
vpc_cidr = "10.0.0.0/16"

# RDS
rds_instance_class     = "db.t3.micro"
rds_allocated_storage  = 20
db_username            = "eldercare_admin"

# Redis
redis_node_type = "cache.t3.micro"

# ECS
frontend_image        = "eldercare-sg/frontend:staging-latest"
backend_image         = "eldercare-sg/backend:staging-latest"
frontend_desired_count = 1
backend_desired_count  = 1

# Domain
domain_name = "staging.eldercare-sg.com"

# Note: Sensitive values should be set via environment variables or AWS Parameter Store
# export TF_VAR_db_password="..."
# export TF_VAR_app_key="..."
# export TF_VAR_calendly_api_key="..."
# export TF_VAR_mailchimp_api_key="..."
# export TF_VAR_twilio_auth_token="..."
# export TF_VAR_sentry_dsn="..."
# export TF_VAR_acm_certificate_arn="..."
