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
