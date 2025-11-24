<header class="app-header">
    <div class="d-flex text-truncate">
        <!-- <img src="{{ asset('images/avt_logo.png') }}" alt="Logo" class="header-logo mr-2"> -->
        <a class="app-header__logo" href="">AVT Hardware Trading</a>
        <!-- <small class="text-light header-subtitle text-truncate" style="max-width: 250px;">
            Wholesale of hardware, electricals, & plumbing supply etc.
        </small> -->
        <!-- Current Date/Time for mobile only -->
        <span id="currentDateTimeMobile" class="text-light font-weight-bold d-block d-md-none mt-1"></span>
    </div>
    <!-- Sidebar toggle button-->
    <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>

    <!-- Navbar Right Menu-->
    <ul class="app-nav ml-auto">

        <!-- Date & Time -->
        <li class="app-nav__item pr-3">
            <span id="currentDateTime" class="text-light font-weight-bold"></span>
        </li>

        <!-- User Menu -->
        <li class="dropdown">
            <a class="app-nav__item dropdown-toggle" href="#" id="userMenu" role="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-user fa-lg"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
                <a class="dropdown-item" href="{{ route('edit_profile') }}">
                    <i class="fa fa-user fa-lg"></i> Edit Profile
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#helpModal">
                    <i class="fa fa-question-circle fa-lg"></i> Help
                </a>
                <a class="dropdown-item" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out fa-lg"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('signout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</header>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper.js (needed for Bootstrap dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Script for date and time -->
<script>
    function updateDateTime() {
        const now = new Date();
        const options = {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        };
        document.getElementById('currentDateTime').innerText = now.toLocaleString('en-US', options);
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();
    $(document).ready(function() {
        // Toggle sidebar
        $('[data-toggle="sidebar"]').click(function(e) {
            e.preventDefault();
            $('.app-sidebar').toggleClass('active');
            $('.app-sidebar__overlay').toggleClass('active');
        });

        // Close sidebar when overlay is clicked
        $('.app-sidebar__overlay').click(function() {
            $('.app-sidebar').removeClass('active');
            $(this).removeClass('active');
        });
    });

</script>
