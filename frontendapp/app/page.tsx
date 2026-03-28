import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { BillboardMapWrapper } from '@/components/map/BillboardMapWrapper';
import { ServicesSection } from '@/components/sections/ServicesSection';
import { AreasCarousel } from '@/components/sections/AreasCarousel';
import { TestimonialsSection } from '@/components/sections/TestimonialsSection';
import { AppDownloadSection } from '@/components/sections/AppDownloadSection';
import { Chatbot } from '@/components/shared/Chatbot';

// TODO: Replace with API call
async function getBillboards() {
  // This would be replaced with actual API call
  // For now, returning empty array
  return [];
}

export default async function Home() {
  const billboards = await getBillboards();

  return (
    <div className="min-h-screen">
      {/* 1. Sticky Transparent Header */}
      <Header />

      <main>
        {/* 2. Full-Screen Map Section - No padding to allow full viewport height */}
        <BillboardMapWrapper billboards={billboards} />

        {/* Sections below map have padding to account for header */}
        <div className="pt-16">
          {/* 3. Services Cards Section */}
          <ServicesSection />

          {/* 4. Tier 1 Areas Carousel */}
          <AreasCarousel />

          {/* 5. Testimonials Section */}
          <TestimonialsSection />

          {/* 6. App Download Links */}
          <AppDownloadSection />
        </div>
      </main>

      {/* 7 & 8. Footer with Widgets and Copyright */}
      <Footer />

      {/* 9. Chatbot Widget */}
      <Chatbot />
    </div>
  );
}
