<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVT Hardware Trading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .navbar-brand { color: #facc15 !important; font-weight: 700; }
        .feature-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }
        #contact { background: #000; color: #fff; }
        #heroSlider .carousel-item img {
            height: 500px; /* adjust height as needed */
            object-fit: cover; /* ensures image covers the container without stretching */
        }

        /* Optional: adjust caption position */
        #heroSlider .carousel-caption {
            bottom: 20px; /* move captions a bit up from the bottom */
        }

        @media (max-width: 768px) {
            #heroSlider .carousel-item img {
                height: 250px; /* smaller height on mobile */
            }
        }
        /* Navbar link hover */
        .navbar-nav .nav-link {
            color: #000 !important; /* default link color */
            position: relative;
            transition: color 0.3s ease;
        }

        /* Change color on hover */
        .navbar-nav .nav-link:hover {
            color: #dc2626 !important; /* red on hover */
        }

        /* Optional: add underline animation */
        .navbar-nav .nav-link::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: #dc2626;
            transition: width 0.3s;
            margin-top: 5px;
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .sticky-top {
            transition: box-shadow 0.3s;
        }

        .sticky-top.scrolled {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
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
                AVT Hardware Trading
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Products</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- BOOTSTRAP SLIDER -->
    <div id="heroSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/slider/1.jpg') }}" class="d-block w-100" alt="Power tools">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h3 class="fw-bold">High-Quality Power Tools</h3>
                    <p>Durable and reliable tools for every professional and DIY project.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/slider/2.jpg') }}" class="d-block w-100" alt="Construction materials">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h3 class="fw-bold">Premium Construction Materials</h3>
                    <p>Everything you need for building, painting, plumbing, and more.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="{{ asset('images/slider/3.jpg') }}" class="d-block w-100" alt="Hardware tools">
                <div class="carousel-caption d-none d-md-block text-start">
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
        <div class="container">
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

    <!-- FOOTER -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row align-items-start">
                <!-- Left column: Logo and Brand -->
                <div class="col-md-4 d-flex align-items-center mb-3 mb-md-0">
                    <img src="{{ asset('images/avt_logo.png') }}" alt="AVT Logo" style="height: 50px; width: auto; margin-right: 10px;">
                    <span class="fs-5 fw-bold">AVT Hardware Trading</span>
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
                        <div class="col-6 mb-3">
                            <h6 class="text-uppercase fw-bold">Contact</h6>
                            <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Brgy. Example, City, Philippines</p>
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i>0912-345-6789</p>
                            <p class="mb-1"><i class="bi bi-envelope me-2"></i>avthardware@email.com</p>
                            <p class="mb-0"><i class="bi bi-globe me-2"></i>www.avthardware.com</p>
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
        AOS.init({ duration: 900, once: true });
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.sticky-top');
            if(window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
