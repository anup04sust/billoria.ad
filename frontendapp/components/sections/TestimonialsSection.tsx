import './testimonials-section.css';

const testimonials = [
  {
    id: 1,
    quote: 'Billoria transformed our advertising strategy. We found the perfect locations for our campaign in minutes!',
    author: 'Sarah Ahmed',
    role: 'Marketing Director',
    company: 'TechCorp Bangladesh',
    avatar: 'https://ui-avatars.com/api/?name=Sarah+Ahmed&background=2563eb&color=fff',
    rating: 5,
  },
  {
    id: 2,
    quote: 'The platform is incredibly user-friendly. Real-time availability and instant booking saved us weeks of coordination time.',
    author: 'Kamal Hossain',
    role: 'Brand Manager',
    company: 'FastFood Chain',
    avatar: 'https://ui-avatars.com/api/?name=Kamal+Hossain&background=f59e0b&color=fff',
    rating: 5,
  },
  {
    id: 3,
    quote: 'As a billboard owner, managing my inventory has never been easier. The analytics and insights are fantastic!',
    author: 'Fatima Rahman',
    role: 'Owner',
    company: 'Prime Outdoor Media',
    avatar: 'https://ui-avatars.com/api/?name=Fatima+Rahman&background=10b981&color=fff',
    rating: 5,
  },
];

export function TestimonialsSection() {
  return (
    <section className="testimonials-section section">
      <div className="container">
        <div className="testimonials-section__header">
          <h2 className="testimonials-section__title">What Our Clients Say</h2>
          <p className="testimonials-section__subtitle">
            Trusted by leading brands and billboard owners across Bangladesh
          </p>
        </div>

        <div className="testimonials-grid">
          {testimonials.map((testimonial) => (
            <article key={testimonial.id} className="testimonial-card">
              <div className="testimonial-card__rating">
                {[...Array(testimonial.rating)].map((_, i) => (
                  <span key={i} className="testimonial-card__star">
                    ⭐
                  </span>
                ))}
              </div>
              <blockquote className="testimonial-card__quote">
                "{testimonial.quote}"
              </blockquote>
              <div className="testimonial-card__author">
                <img
                  src={testimonial.avatar}
                  alt={testimonial.author}
                  className="testimonial-card__avatar"
                />
                <div className="testimonial-card__info">
                  <div className="testimonial-card__name">{testimonial.author}</div>
                  <div className="testimonial-card__role">
                    {testimonial.role} at {testimonial.company}
                  </div>
                </div>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
