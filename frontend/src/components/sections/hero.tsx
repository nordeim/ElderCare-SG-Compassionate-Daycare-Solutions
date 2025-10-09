'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Play, X } from 'lucide-react'

type HeroCtaLabels = {
  primary: string
  video: string
}

export type HeroProps = {
  title: string
  subtitle: string
  cta: HeroCtaLabels
}

const Hero = ({ title, subtitle, cta }: HeroProps) => {
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
        <h1 className="text-4xl md:text-6xl font-playfair font-bold mb-6">{title}</h1>
        <p className="text-xl md:text-2xl mb-8 text-eldercare-off-white">{subtitle}</p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Button variant="cta" size="lg" asChild>
            <a href="#booking">{cta.primary}</a>
          </Button>
          <Button
            variant="outline"
            size="lg"
            className="bg-white/20 border-white text-white hover:bg-white/30"
            onClick={() => setIsVideoOpen(true)}
          >
            <Play className="mr-2 h-4 w-4" />
            {cta.video}
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
