#!/bin/bash
# scripts/05-create-components.sh

set -e

echo "🧩 Creating initial components and pages..."

cd frontend

# Create UI components
mkdir -p src/components/ui

# Create Button component
cat > src/components/ui/button.tsx << 'EOF'
import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-eldercare-deep-blue text-white hover:bg-eldercare-deep-blue/90",
        destructive:
          "bg-destructive text-destructive-foreground hover:bg-destructive/90",
        outline:
          "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
        secondary:
          "bg-eldercare-soft-amber text-eldercare-deep-blue hover:bg-eldercare-soft-amber/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-eldercare-gold underline-offset-4 hover:underline",
        cta: "bg-eldercare-gold text-eldercare-deep-blue font-semibold hover:bg-eldercare-gold/90 shadow-md",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm: "h-9 rounded-md px-3",
        lg: "h-11 rounded-md px-8",
        icon: "h-10 w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : "button"
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      />
    )
  }
)
Button.displayName = "Button"

export { Button, buttonVariants }
EOF

# Create Card component
cat > src/components/ui/card.tsx << 'EOF'
import * as React from "react"

import { cn } from "@/lib/utils"

const Card = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn(
      "rounded-lg border bg-card text-card-foreground shadow-sm",
      className
    )}
    {...props}
  />
))
Card.displayName = "Card"

const CardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-6", className)}
    {...props}
  />
))
CardHeader.displayName = "CardHeader"

const CardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn(
      "text-2xl font-semibold leading-none tracking-tight",
      className
    )}
    {...props}
  />
))
CardTitle.displayName = "CardTitle"

const CardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-sm text-muted-foreground", className)}
    {...props}
  />
))
CardDescription.displayName = "CardDescription"

const CardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-6 pt-0", className)} {...props} />
))
CardContent.displayName = "CardContent"

const CardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center p-6 pt-0", className)}
    {...props}
  />
))
CardFooter.displayName = "CardFooter"

export { Card, CardHeader, CardFooter, CardTitle, CardDescription, CardContent }
EOF

# Create utility function
mkdir -p src/lib
cat > src/lib/utils.ts << 'EOF'
import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
EOF

# Create layout components
mkdir -p src/components/layout

# Create Header component
cat > src/components/layout/header.tsx << 'EOF'
'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Menu, X } from 'lucide-react'

const Header = () => {
  const [isScrolled, setIsScrolled] = useState(false)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)

  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 10) {
        setIsScrolled(true)
      } else {
        setIsScrolled(false)
      }
    }

    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        isScrolled
          ? 'bg-white/80 backdrop-blur-md shadow-sm'
          : 'bg-transparent'
      }`}
    >
      <div className="container mx-auto px-6 py-4 flex items-center justify-between">
        <div className="flex items-center">
          <Link href="/" className="text-2xl font-playfair font-bold text-eldercare-deep-blue">
            ElderCare SG
          </Link>
        </div>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex items-center space-x-8">
          <Link href="#programs" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Programs
          </Link>
          <Link href="#philosophy" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Our Philosophy
          </Link>
          <Link href="#testimonials" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Testimonials
          </Link>
          <Link href="#contact" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            Contact
          </Link>
          <Button variant="cta" asChild>
            <Link href="#booking">Book Visit</Link>
          </Button>
        </nav>

        {/* Mobile Menu Button */}
        <button
          className="md:hidden text-eldercare-deep-blue"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          aria-label="Toggle menu"
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile Navigation */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white border-t">
          <nav className="container mx-auto px-6 py-4 flex flex-col space-y-4">
            <Link
              href="#programs"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Programs
            </Link>
            <Link
              href="#philosophy"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Our Philosophy
            </Link>
            <Link
              href="#testimonials"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Testimonials
            </Link>
            <Link
              href="#contact"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Contact
            </Link>
            <Button variant="cta" asChild className="w-full">
              <Link href="#booking" onClick={() => setIsMobileMenuOpen(false)}>
                Book Visit
              </Link>
            </Button>
          </nav>
        </div>
      )}
    </header>
  )
}

export default Header
EOF

# Create Footer component
cat > src/components/layout/footer.tsx << 'EOF'
import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Mail, Phone, MapPin } from 'lucide-react'

const Footer = () => {
  return (
    <footer className="bg-eldercare-deep-blue text-white">
      <div className="container mx-auto px-6 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Contact Information */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Contact Us</h3>
            <div className="space-y-3">
              <div className="flex items-center space-x-3">
                <Phone size={18} />
                <span>+65 6123 4567</span>
              </div>
              <div className="flex items-center space-x-3">
                <Mail size={18} />
                <span>info@eldercare-sg.example.com</span>
              </div>
              <div className="flex items-center space-x-3">
                <MapPin size={18} />
                <span>123 Care Lane, Singapore 123456</span>
              </div>
            </div>
          </div>

          {/* Newsletter */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Stay Updated</h3>
            <p className="mb-4 text-eldercare-off-white">
              Subscribe to our newsletter for updates on our programs and services.
            </p>
            <form className="flex flex-col space-y-2">
              <input
                type="email"
                placeholder="Your email address"
                className="px-4 py-2 rounded-md text-eldercare-deep-blue"
                required
              />
              <Button variant="secondary" type="submit">
                Subscribe
              </Button>
            </form>
          </div>

          {/* Certifications */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">Certifications</h3>
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">MOH Certified</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">ISO 9001</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">Eldercare Accredited</span>
              </div>
              <div className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                <span className="text-sm">SG Verified</span>
              </div>
            </div>
          </div>
        </div>

        <div className="border-t border-white/20 mt-8 pt-8 text-center text-eldercare-off-white">
          <p>&copy; {new Date().getFullYear()} ElderCare SG. All rights reserved.</p>
          <div className="mt-4 space-x-4">
            <Link href="/privacy" className="hover:text-eldercare-gold transition-colors">
              Privacy Policy
            </Link>
            <Link href="/terms" className="hover:text-eldercare-gold transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer
EOF

# Create section components
mkdir -p src/components/sections

# Create Hero section
cat > src/components/sections/hero.tsx << 'EOF'
'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Play, X } from 'lucide-react'

const Hero = () => {
  const [isVideoOpen, setIsVideoOpen] = useState(false)

  return (
    <section className="relative h-screen flex items-center justify-center overflow-hidden">
      {/* Background Video */}
      <div className="absolute inset-0 z-0">
        <video
          className="w-full h-full object-cover"
          autoPlay
          muted
          loop
          playsInline
        >
          <source src="/videos/hero-video.mp4" type="video/mp4" />
          <source src="/videos/hero-video.webm" type="video/webm" />
        </video>
        <div className="absolute inset-0 bg-eldercare-deep-blue/60"></div>
      </div>

      {/* Content */}
      <div className="relative z-10 text-center text-white px-6 max-w-4xl mx-auto">
        <h1 className="text-4xl md:text-6xl font-playfair font-bold mb-6">
          Compassionate Daycare Solutions for Your Loved Ones
        </h1>
        <p className="text-xl md:text-2xl mb-8 text-eldercare-off-white">
          Providing a safe, engaging, and nurturing environment for seniors in Singapore
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Button variant="cta" size="lg" asChild>
            <a href="#booking">Book Visit</a>
          </Button>
          <Button
            variant="outline"
            size="lg"
            className="bg-white/20 border-white text-white hover:bg-white/30"
            onClick={() => setIsVideoOpen(true)}
          >
            <Play className="mr-2 h-4 w-4" />
            Watch Tour
          </Button>
        </div>
      </div>

      {/* Video Modal */}
      {isVideoOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
          <div className="relative w-full max-w-4xl">
            <button
              className="absolute -top-10 right-0 text-white hover:text-eldercare-gold transition-colors"
              onClick={() => setIsVideoOpen(false)}
              aria-label="Close video"
            >
              <X size={24} />
            </button>
            <div className="aspect-video">
              <iframe
                src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                title="ElderCare SG Virtual Tour"
                className="w-full h-full rounded-md"
                allowFullScreen
              ></iframe>
            </div>
          </div>
        </div>
      )}
    </section>
  )
}

export default Hero
EOF

# Create Program Highlights section
cat > src/components/sections/program-highlights.tsx << 'EOF'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Heart, Activity, Users } from 'lucide-react'

const ProgramHighlights = () => {
  const programs = [
    {
      icon: <Heart className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Day Programs",
      description: "Engaging daily activities designed to promote physical and mental well-being.",
      color: "bg-eldercare-soft-amber"
    },
    {
      icon: <Activity className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Wellness",
      description: "Comprehensive health monitoring and wellness programs tailored to individual needs.",
      color: "bg-eldercare-off-white"
    },
    {
      icon: <Users className="h-8 w-8 text-eldercare-calming-green" />,
      title: "Family Support",
      description: "Resources and support for families to ensure the best care for their loved ones.",
      color: "bg-eldercare-soft-amber"
    }
  ]

  return (
    <section id="programs" className="py-20 bg-eldercare-off-white">
      <div className="container mx-auto px-6">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-playfair font-bold text-eldercare-deep-blue mb-4">
            Our Programs
          </h2>
          <p className="text-lg text-eldercare-slate-gray-2 max-w-2xl mx-auto">
            We offer a range of programs designed to meet the diverse needs of our elderly community.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {programs.map((program, index) => (
            <Card key={index} className={`${program.color} border-none shadow-md hover:shadow-lg transition-shadow`}>
              <CardHeader className="text-center">
                <div className="flex justify-center mb-4">
                  {program.icon}
                </div>
                <CardTitle className="text-xl text-eldercare-deep-blue">
                  {program.title}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-center text-eldercare-slate-gray-1">
                  {program.description}
                </CardDescription>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  )
}

export default ProgramHighlights
EOF

# Create main layout
mkdir -p src/app
cat > src/app/layout.tsx << 'EOF'
import type { Metadata } from 'next'
import { Inter, Playfair_Display } from 'next/font/google'
import './globals.css'

const inter = Inter({ subsets: ['latin'], variable: '--font-inter' })
const playfairDisplay = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  weight: ['400', '600', '700']
})

export const metadata: Metadata = {
  title: 'ElderCare SG - Compassionate Daycare Solutions',
  description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
  keywords: ['elderly care', 'daycare', 'senior care', 'Singapore', 'compassionate care'],
  authors: [{ name: 'ElderCare SG Team' }],
  openGraph: {
    title: 'ElderCare SG - Compassionate Daycare Solutions',
    description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
    images: ['/images/og-image.jpg'],
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className={`${inter.variable} ${playfairDisplay.variable}`}>
      <body className="font-inter bg-eldercare-off-white text-eldercare-slate-gray-1">
        {children}
      </body>
    </html>
  )
}
EOF

# Create global styles
cat > src/app/globals.css << 'EOF'
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96%;
    --secondary-foreground: 222.2 84% 4.9%;
    --muted: 210 40% 96%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96%;
    --accent-foreground: 222.2 84% 4.9%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 84% 4.9%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 94.1%;
  }
}

@layer base {
  * {
    @apply border-border;
  }
  body {
    @apply bg-background text-foreground;
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Focus styles for accessibility */
.focus-visible:focus {
  @apply outline-2 outline-offset-2 outline-eldercare-gold;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
EOF

# Create home page
cat > src/app/page.tsx << 'EOF'
import Header from '@/components/layout/header'
import Footer from '@/components/layout/footer'
import Hero from '@/components/sections/hero'
import ProgramHighlights from '@/components/sections/program-highlights'

export default function Home() {
  return (
    <main className="min-h-screen">
      <Header />
      <Hero />
      <ProgramHighlights />
      <Footer />
    </main>
  )
}
EOF

echo "✅ Initial components and pages created successfully!"
