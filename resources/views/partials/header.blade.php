<header class="app-header d-flex align-items-center justify-content-between px-3">
    <!-- Left side: Logo + Mobile Date -->
    <div class="d-flex align-items-center">
        <a class="app-header__logo mr-2" href="">AVT Hardware Trading</a>
        <!-- Mobile-only Date/Time -->
        <span id="currentDateTimeMobile" class="text-light font-weight-bold d-block d-md-none ml-2"></span>
    </div>

    <!-- Sidebar toggle (always visible) -->
    <a class="app-sidebar__toggle ml-2" href="#" data-toggle="sidebar" aria-label="Hide Sidebar">
        <!-- <i class="fa fa-bars fa-lg text-light"></i> -->
    </a>

    <!-- Right side: Nav -->
    <ul class="app-nav d-flex align-items-center ml-auto mb-0">
        <!-- Desktop Date/Time -->
        <li class="app-nav__item pr-3 d-none d-md-block">
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
        $('[data-toggle="sidebar"]').click(function(e) {
            e.preventDefault();
            $('.app-sidebar').toggleClass('active');
            $('.app-sidebar__overlay').toggleClass('active');
        });

        $('.app-sidebar__overlay').click(function() {
            $('.app-sidebar').removeClass('active');
            $(this).removeClass('active');
        });
    });
</script>
