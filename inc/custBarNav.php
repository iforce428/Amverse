<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-navy">
    <div class="container px-4 px-lg-5">
        <button class="navbar-toggler btn btn-sm" type="button" data-bs-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="./">
            <img src="<?php echo validate_image($_settings->info('logo')) ?>" width="30" height="30" class="d-inline-block align-top" alt="" loading="lazy">
            <?php echo $_settings->info('short_name') ?>
        </a>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <!-- Dropdown for logged-in users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../amverse/customer/customer_profile.php">View Profile</a></li>
                            <li><a class="dropdown-item" href="../amverse/customer/customer_logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Sign In button for guests -->
                    <li class="nav-item">
                        <a class="nav-link" href="customer/customer_signin.php">Sign In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
    // Ensure Bootstrap dropdown functionality works
    $(function(){
        // Dropdown toggle initialization
        $('.dropdown-toggle').dropdown();

        // Navbar responsiveness handling
        $('#navbarResponsive').on('show.bs.collapse', function () {
            $('#mainNav').addClass('navbar-shrink');
        });
        $('#navbarResponsive').on('hidden.bs.collapse', function () {
            if ($('body').offset().top == 0)
                $('#mainNav').removeClass('navbar-shrink');
        });
    });

    // Optional: Handle form submission for search if needed
    $('#search-form').submit(function(e){
        e.preventDefault();
        var sTxt = $('[name="search"]').val();
        if (sTxt != '')
            location.href = './?p=products&search=' + sTxt;
    });
</script>
