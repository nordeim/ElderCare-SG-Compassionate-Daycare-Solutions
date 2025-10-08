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
