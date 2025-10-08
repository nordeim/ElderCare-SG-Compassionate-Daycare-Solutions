# Development stage
FROM node:18-alpine AS development

WORKDIR /app

# Install dependencies
COPY frontend/package*.json ./
RUN npm ci

# Copy application code
COPY frontend/ .

# Expose port
EXPOSE 3000

# Development command
CMD ["npm", "run", "dev"]

# Production build stage
FROM node:18-alpine AS builder

WORKDIR /app

COPY frontend/package*.json ./
RUN npm ci

COPY frontend/ .

# Build Next.js application
RUN npm run build

# Production stage
FROM node:18-alpine AS production

WORKDIR /app

ENV NODE_ENV=production

# Copy package files and install production dependencies only
COPY frontend/package*.json ./
RUN npm ci --only=production && npm cache clean --force

# Copy built application from builder
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/public ./public
COPY --from=builder /app/next.config.js ./

EXPOSE 3000

USER node

CMD ["npm", "start"]
