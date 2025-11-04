<header class="app-header">
    <a class="app-header__logo" href="">AVT Hardware Trading</a>

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
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
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
</script>
