<?php include 'app/views/shares/header.php'; ?>

<section class="vh-100 gradient-custom" style="background: linear-gradient(to bottom right, #6a11cb, #2575fc);">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-dark text-white shadow" style="border-radius: 1rem;">
                    <div class="card-body p-5 text-center">
                        <form action="/lab_1/Account/checklogin" method="post">
                            <div class="mb-md-5 mt-md-4 pb-5">
                                <h2 class="fw-bold mb-4 text-uppercase">Login</h2>
                                <p class="text-white-50 mb-4">Please enter your username and password!</p>

                                <div class="form-outline form-white mb-4">
                                    <label class="form-label" for="username">Username</label>
                                    <input type="text" id="username" name="username" class="form-control form-control-lg" placeholder="Enter your username" required />
                                </div>

                                <div class="form-outline form-white mb-4">
                                    <label class="form-label" for="password">Password</label>
                                    <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Enter your password" required />
                                </div>

                                <div class="d-flex justify-content-between mb-4">
                                    <a href="#!" class="text-white-50 small">Forgot password?</a>
                                </div>

                                <button class="btn btn-outline-light btn-lg w-100 mb-4" type="submit">Login</button>

                                <div class="d-flex justify-content-center text-center mt-4">
                                    <a href="#!" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#!" class="text-white mx-3"><i class="fab fa-twitter"></i></a>
                                    <a href="#!" class="text-white ms-3"><i class="fab fa-google"></i></a>
                                </div>
                            </div>

                            <div>
                                <p class="mb-0">Don't have an account? 
                                    <a href="/lab_1/Account/register" class="text-white-50 fw-bold">Sign Up</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'app/views/shares/footer.php'; ?>
