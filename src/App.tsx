import { useMemo, useState } from 'react';

const navItems = ['Home', 'Projects', 'Marketing', 'Skills', 'About', 'Contact'];

const marketingItems = [
  'HND Quantity Surveying introduction session flyer',
  'AI-Aqsa Sports Club seminar series flyer',
  'AI-Aqsa Sports Club seminar series cover design',
  'AI-Hilal Travels Umrah package flyer for January',
  'AI-Hilal Travels Umrah package flyer for July',
  'AI-Hilal Travels Umrah package flyer with pricing',
  'Restaurant trifold menu front cover',
  'Restaurant trifold menu inside spread',
];

const serviceList = ['Banner Design', 'Brochure & Flyer Design', 'Social Media Poster Design', 'CV Making & Typing'];

export default function App() {
  const [expanded, setExpanded] = useState(true);

  const visibleItems = useMemo(
    () => (expanded ? marketingItems : marketingItems.slice(0, 3)),
    [expanded],
  );

  return (
    <div className="app-shell">
      <header className="topbar">
        <nav className="topnav" aria-label="Main navigation">
          <div className="logo" aria-label="NM Nufail">
            NM <span>Nufail</span>
          </div>

          <ul className="nav-menu">
            {navItems.map((item) => (
              <li key={item}>
                <a href="#" className={item === 'Marketing' ? 'nav-link active' : 'nav-link'}>
                  {item}
                </a>
              </li>
            ))}
          </ul>
        </nav>
      </header>

      <main className="marketing-shell">
        <section className="marketing-layout">
          <div className="marketing-list" aria-label="Marketing flyers">
            {visibleItems.map((item, index) => (
              <article className={`flyer-item ${index === 0 ? 'featured' : ''}`} key={item}>
                <div className="flyer-icon" aria-hidden="true" />
                <span>{item}</span>
              </article>
            ))}
          </div>

          <div className="marketing-side">
            <article className="service-callout">
              <div className="service-header">
                <div className="mini-badge" aria-hidden="true" />
                <div className="service-title">Register Now</div>
                <div className="phone-box">
                  <span>077 88 71 81</span>
                </div>
              </div>

              <div className="service-body">
                <div className="service-checks">
                  {serviceList.map((service) => (
                    <div key={service} className="service-row">
                      <span className="check-box" aria-hidden="true" />
                      <span>{service}</span>
                    </div>
                  ))}
                </div>
              </div>
            </article>

            <article className="luxury-panel">
              <div className="luxury-topline">Riley Luxe</div>
              <div className="luxury-title">STRIKE A POSE, BUT MAKE IT LUXE. STUDIO SESSION FOR THE BOLD &amp; BEAUTIFUL</div>
            </article>
          </div>
        </section>

        <button type="button" className="toggle-button" onClick={() => setExpanded((value) => !value)}>
          {expanded ? 'Show Less' : 'Show More Designs'}
        </button>

      </main>
    </div>
  );
}
