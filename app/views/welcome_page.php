<!--welcome_page.php -->
<!DOCTYPE html> 

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>hubIT.online | Smarter Tech. Simpler Solutions.</title>
  <meta name="description" content="HubIT.online - Insights, Maintain, Sense. Smarter enterprise IoT and IT solutions for industries of tomorrow.">
  <meta name="author" content="hubIT.online">
  <link rel="icon" href="/mes/app/Assets/img/favicon.ico" type="image/x-icon">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    color: #222;
    /* background: linear-gradient(135deg, #0f172a, #1e293b, #0f172a); */
    position: relative;
    overflow-x: hidden;
  }

  /* Circuit overlay background */
  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('/mes/app/Assets/img/hub.png') no-repeat center center;
    background-size: cover;
    /* opacity: 0.05; */
    pointer-events: none;
    z-index: 0;
  }

  header {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .logo img {
    height: 80px;
    display: block;
  }

  main {
    padding: 3rem 1rem;
    position: relative;
    z-index: 1;
  }

  .hero {
    text-align: center;
    margin: 2rem auto 3rem;
    max-width: 850px;
    color: #f8fafc;
  }

  .hero h1 {
    font-size: 2.8rem;
    margin-bottom: 0.8rem;
    font-weight: 700;
    background: linear-gradient(90deg, #38bdf8, #3b82f6, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text; /* Add standard version for better support */
  }

  .hero p {
    font-size: 1.2rem;
    color: #cbd5e1;
    margin-bottom: 2rem;
  }

  .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
  }

  .card {
    background: rgba(186, 234, 246, 0.25);
    border: 1px solid #e2e8f0;
    /* opacity: 0.85; */
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.2);
  }

  .card h2 {
    margin-top: 0;
    font-size: 1.6rem;
    color: #E2EAF4;
  }

  .card p {
    color: #cbd5e1;
    font-size: 1rem;
    margin-bottom: 1.5rem;
    min-height: 60px;
  }

  .button {
    display: inline-block;
    padding: 0.8rem 1.4rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    color: #fff;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    transition: transform 0.2s ease, background 0.3s ease;
  }

  .button:hover {
    background: linear-gradient(90deg, #1e40af, #2563eb);
    transform: scale(1.05);
  }

  footer {
    text-align: center;
    padding: 2rem;
    font-size: 0.9rem;
    color: #94a3b8;
    margin-top: 4rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(6px);
  }
</style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="/mes/app/Assets/img/hubIT_logo-v5.png" alt="hubIT.online Logo" />
    </div>
  </header>

  <main >
    <section class="hero">
      <h1>Smarter Tech. Simpler Solutions.</h1>
      <p>HubIT.online unifies real-time insights, asset maintenance, and IoT monitoring — empowering enterprises with clarity and control.</p>
    </section>

    <section class="choices">
      <div class="cards">
        <!-- Register Card -->
        <article class="card">
          <h2>Register</h2>
          <p>Create your HubIT.online account to unlock secure, enterprise-grade tools.</p>
          <a class="button" href="/mes/register">Create Account</a>
        </article>

        <!-- Sign-in Card -->
        <article class="card">
          <h2>Sign-in</h2>
          <p>Already have access? Sign in to continue managing your enterprise systems.</p>
          <a class="button" href="/mes/signin">Sign In</a>
        </article>

        <!-- Demo Card -->
        <article class="card">
          <h2>Demo</h2>
          <p>Explore HubIT.online with interactive dashboards and IoT demos — no setup needed.</p>
          <a class="button" href="demo/demo_dashboard">Try Demo</a>
        </article>
      </div>
    </section>
  </main>

  <footer>
    &copy; 2025 hubIT.online. All rights reserved.
  </footer>
</body>
</html>
