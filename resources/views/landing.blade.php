<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Essential Meta Tags -->
    <meta name="description" content="AVT Hardware Trading offers high-quality power tools, construction materials, plumbing supplies, and electrical items at competitive wholesale and retail prices. Your trusted hardware supplier in the Philippines.">
    <meta name="keywords" content="Wholesale of hardware, electricals, & plumbing supply etc., hardware store philippines, power tools, construction materials, plumbing supplies, electrical supplies, wholesale hardware, AVT Hardware, hardware trading, hardware, wholesale">
    <meta name="author" content="AVT Hardware Trading">

    <!-- Open Graph for Facebook / Messenger / Viber -->
    <meta property="og:title" content="AVT Hardware Trading - Quality Hardware, Tools & Construction Supplies">
    <meta property="og:description" content="Discover premium tools, plumbing supplies, electrical items, and construction materials. Wholesale & retail hardware supplier you can trust.">
    <meta property="og:image" content="{{ asset('images/avt_logo.png') }}">
    <meta property="og:url" content="https://www.avthardware.com">
    <meta property="og:type" content="website">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AVT Hardware Trading">
    <meta name="twitter:description" content="Your trusted hardware supplier for tools, electricals, plumbing, and construction materials.">
    <meta name="twitter:image" content="{{ asset('images/avt_logo.png') }}">

    <!-- Mobile & Browser -->
    <meta name="theme-color" content="#dc2626">
    <meta name="robots" content="index, follow">

    <title>AVT Hardware Trading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#" style="color: #dc2626 !important;">
                <img src="{{ asset('images/avt_logo.png') }}" alt="AVT Logo"
                    style="height: 45px; width: auto; margin-right: 10px;">

                <div class="d-flex flex-column">
                    <span class="fw-bold">AVT Hardware Trading</span>
                    <small class="text-muted" style="margin-top: -3px;font-size:11px;">
                        Wholesale of hardware, electricals, & plumbing supply etc.
                    </small>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- BOOTSTRAP SLIDER -->
    <div id="heroSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/slider/1.jpg') }}" class="d-block w-100" alt="Power tools">
                <div class="carousel-caption text-start">
                    <h3 class="fw-bold">High-Quality Power Tools</h3>
                    <p>Durable and reliable tools for every professional and DIY project.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/slider/2.jpg') }}" class="d-block w-100" alt="Construction materials">
                <div class="carousel-caption text-start">
                    <h3 class="fw-bold">Premium Construction Materials</h3>
                    <p>Everything you need for building, painting, plumbing, and more.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/slider/3.jpg') }}" class="d-block w-100" alt="Hardware tools">
                <div class="carousel-caption text-start">
                    <h3 class="fw-bold">Complete Hardware Solutions</h3>
                    <p>Tools, equipment, and supplies for professionals and homeowners.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>


    <!-- ABOUT SECTION -->
    <section id="about" class="py-5 text-center container" data-aos="fade-up">
        <h2 class="fw-bold mb-4"><i class="bi bi-tools me-2"></i>Welcome to AVT Hardware Trading</h2>
        <p class="lead mb-4">
            Your trusted partner for high-quality tools, equipment, and construction materials crafted for professionals, builders, and homeowners.
        </p>
        <div class="row justify-content-center g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <i class="bi bi-hammer fs-2 text-danger mb-2"></i>
                <h5>Wide Range of Products</h5>
                <p>From hand tools to power tools, plumbing, electrical supplies, and construction materials, we have everything you need in one place.</p>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <i class="bi bi-shield-check fs-2 text-danger mb-2"></i>
                <h5>Trusted Quality</h5>
                <p>All our products are sourced from reliable manufacturers and tested for durability and safety for professional use.</p>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <i class="bi bi-cash-stack fs-2 text-danger mb-2"></i>
                <h5>Competitive Pricing</h5>
                <p>We offer affordable pricing for bulk orders and retail purchases without compromising on quality.</p>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section id="features" class="py-5 bg-light">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center fw-bold mb-5" data-aos="fade-up">Our Products</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <img src="{{ asset('images/slider/powertools.jpg') }}" class="card-img-top" alt="Power Drill">
                        <div class="card-body">
                            <h5 class="card-title">Power Tools</h5>
                            <p class="card-text">Drills, saws, grinders, and other essential tools for professionals.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <img src="{{ asset('images/slider/brushes.jpg') }}" class="card-img-top" alt="Paint Supplies">
                        <div class="card-body">
                            <h5 class="card-title">Paint & Supplies</h5>
                            <p class="card-text">High-quality paints, brushes, rollers, and painting accessories.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <img src="{{ asset('images/slider/plumbing.jpg') }}" class="card-img-top" alt="Plumbing Supplies">
                        <div class="card-body">
                            <h5 class="card-title">Plumbing & Hardware</h5>
                            <p class="card-text">Pipes, fittings, faucets, and all plumbing essentials for construction.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="contact" class="py-5 bg-light">
        <div class="container" data-aos="fade-up">
            <h2 class="text-center fw-bold mb-3 text-dark">
                <i class="bi bi-chat-dots me-2 text-danger"></i>Get in Touch With Us
            </h2>
            <p class="text-center mb-5 lead text-dark">
                Have questions, need a quote, or looking for a reliable hardware supplier?  
                We're here to help! Whether you're a contractor, business owner, or homeowner,  
                AVT Hardware Trading is ready to provide the tools, supplies, and support you need.
            </p>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-3">What We Can Offer</h5>
                        <ul class="mb-4">
                            <li>Wholesale and retail pricing for tools, plumbing, and electrical supplies</li>
                            <li>Reliable sourcing for construction materials</li>
                            <li>Bulk order support for contractors and businesses</li>
                            <li>Fast response for inquiries and quotations</li>
                        </ul>

                        <div class="text-center mt-4">
                            <a href="tel:09368834275" class="btn btn-danger px-4 py-2 me-2">
                                <i class="bi bi-telephone me-1"></i> Call Us
                            </a>
                            <a href="mailto:avthardware@yahoo.com" class="btn btn-outline-danger px-4 py-2">
                                <i class="bi bi-envelope me-1"></i> Email Us
                            </a>
                        </div>

                        <p class="text-center mt-4 mb-0 text-muted">
                            We’d love to assist you with your hardware and construction supply needs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row align-items-start">
                <!-- Left column: Logo and Brand -->
                <div class="col-md-4 text-center text-md-start mb-3">
                    <img src="{{ asset('images/avt_logo.png') }}" alt="AVT Logo" style="height: 50px; width: auto; margin-right: 10px;">
                    <div class="d-flex flex-column">
                        <span class="fw-bold">AVT Hardware Trading</span>
                        <small class="text-muted" style="margin-top: -3px;font-size:11px;color:white!important;">
                            Wholesale of hardware, electricals, & plumbing supply etc.
                        </small>
                    </div>
                </div>

                <!-- Right column: Links and Contact -->
                <div class="col-md-8">
                    <div class="row">
                        <!-- Links -->
                        <div class="col-6 mb-3">
                            <h6 class="text-uppercase fw-bold">Links</h6>
                            <ul class="list-unstyled">
                                <li><a href="#about" class="text-white text-decoration-none">About Us</a></li>
                                <li><a href="#features" class="text-white text-decoration-none">Products</a></li>
                            </ul>
                        </div>

                        <!-- Contact Info -->
                        <div class="col-6 col-sm-6 col-12 mb-3 text-center text-md-start">
                            <h6 class="text-uppercase fw-bold">Contact</h6>
                            <!-- <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Brgy. Example, City, Philippines</p> -->
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i>0936-8834-275 / 0999-3669-539</p>
                            <p class="mb-1"><i class="bi bi-envelope me-2"></i>avthardware@yahoo.com</p>
                            <p class="mb-0"><i class="bi bi-globe me-2"></i>www.avthardwaretrading.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="text-center mt-3 border-top border-secondary pt-3">
                &copy; 2025 AVT Hardware Trading. All rights reserved.
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 900,
            once: false // allow animations to replay
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function () {
                setTimeout(() => {
                    AOS.refresh();
                }, 500);
            });
        });
    </script>
</body>
</html>
