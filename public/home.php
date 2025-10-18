<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EU Projects in Montenegro</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #003399 0%, #0055cc 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }
        
        .hero-btn-primary {
            background-color: #FFCC00;
            color: #003399;
        }
        
        .hero-btn-primary:hover {
            background-color: #e6b800;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 204, 0, 0.4);
        }
        
        .hero-btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .hero-btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        /* Features Section */
        .features {
            padding: 4rem 0;
            background-color: #f9fafb;
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .features h2 {
            text-align: center;
            color: #003399;
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 51, 153, 0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            color: #003399;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, #003399 0%, #0055cc 100%);
            color: white;
            padding: 3rem 0;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-item h4 {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-item .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFCC00;
        }
        
        /* About Section */
        .about {
            padding: 4rem 0;
            background-color: white;
        }
        
        .about-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .about h2 {
            color: #003399;
            font-size: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .about p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        .home-footer {
            background-color: #003399;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .home-footer p {
            opacity: 0.9;
        }
        
        .home-footer a {
            color: #FFCC00;
            text-decoration: none;
        }
        
        .home-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .features h2,
            .about h2 {
                font-size: 2rem;
            }
            
            .stat-item .stat-value {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h1>🇪🇺 EU Projects in Montenegro</h1>
            <p>Explore EU-funded projects contributing to the development and progress of Montenegro</p>
            <div class="hero-buttons">
                <a href="/public.php" class="hero-btn hero-btn-primary">Explore Projects</a>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h4>EU Investment</h4>
                    <div class="stat-value">€ Millions</div>
                </div>
                <div class="stat-item">
                    <h4>Active Projects</h4>
                    <div class="stat-value">100+</div>
                </div>
                <div class="stat-item">
                    <h4>Sectors</h4>
                    <div class="stat-value">15+</div>
                </div>
                <div class="stat-item">
                    <h4>Municipalities</h4>
                    <div class="stat-value">20+</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features">
        <div class="features-container">
            <h2>What You Can Find Here</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Search & Filter</h3>
                    <p>Easily find projects by sector, municipality, program, timeline, and more using our advanced filtering system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Project Details</h3>
                    <p>Access comprehensive information about each EU-funded project including budgets, timelines, and beneficiaries.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🗺️</div>
                    <h3>Interactive Map</h3>
                    <p>Visualize projects across Montenegro with an interactive map showing geographical distribution.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Statistics</h3>
                    <p>View real-time statistics on total funding, active projects, and development impact across regions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌍</div>
                    <h3>All Sectors</h3>
                    <p>Discover projects across various sectors: infrastructure, environment, education, health, and more.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Timeline View</h3>
                    <p>Track ongoing and completed projects, filter by start and end years to see development progress.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="about">
        <div class="about-container">
            <h2>About EU Support to Montenegro</h2>
            <p>
                The European Union provides significant financial and technical support to Montenegro 
                as part of its accession process. These projects contribute to the country's socio-economic 
                development, strengthen democratic institutions, and help align Montenegro with EU standards.
            </p>
            <p>
                This dashboard provides transparent access to information about EU-funded projects, 
                helping citizens, researchers, and stakeholders understand how EU assistance is 
                contributing to Montenegro's development.
            </p>
            <a href="/public.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
                <span class="btn-text">View All Projects</span>
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="home-footer">
        <div class="footer-disclaimer">
            <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                This website was created and maintained with the financial support of the European Union. 
                Its contents are the sole responsibility of the Europe House and do not necessarily reflect 
                the views of the European Union.
            </p>
        </div>
        <p>&copy; 2025 EU Projects in Montenegro.</p>
    </footer>
</body>
</html>
