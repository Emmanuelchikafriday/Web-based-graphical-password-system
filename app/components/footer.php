<?php $forbidden_contact_section_pages = ['contact'] ?>
<footer>
	<?php if (!in_array(uriSegment(2), $forbidden_contact_section_pages)): ?>
		<section id="contact" class="site-primary text-white">
			<div class="container">
				<div class="content">
					<div class="text-center">
						<h3 class="mb-4">Partnering Environmental Science</h3>
						<p class="text-white-50 mb-3">Looking for a First-Class Scientific Partner?</p>
						<a href="#" class="site-text-primary btn btn-sm btn-light px-3">Contact Us.</a>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>
	
	<div class="container mt-5">
		<div class="row">
			<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
				<div class="h-100 w-100">
					<a href="./"><img class="mb-2" src="<?= asset('meta/logo.png') ?>" alt="logo"/></a>
					<p>Making the world a better place through constructing elegant hierachies</p>
					<p>Telephone +2348177668572, +2348033030049</p>
					<p>e-mails: info@tpinigeria.com</p>
					<div class="d-flex">
						<a href="https://www.facebook.com" target="_blank"><i class="text-primary mx-2 fab fa-facebook fa-2x"></i></a>
						<a href="https://www.twitter.com" target="_blank"><i class="text-info mx-2 fab fa-twitter fa-2x"></i></a>
						<a href="https://www.instagram.com" target="_blank"><i class="text-danger mx-2 fab fa-instagram fa-2x"></i></a>
						<a href="https://www.github.com" target="_blank"><i class="text-dark mx-2 fab fa-github fa-2x"></i></a>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
				<p class="text-muted">OUR LOCATIONS</p>
				<div class="h-100 w-100">
					<ul class="ps-3">
						<li class="mb-3">
							Head Office: 52/54 Oluwaleyimu Street, Off Toyin Street, Ikeja, Lagos State, Nigeria.
						</li>
						<li class="mb-3">
							Rivers State, Port Harcourt Lab
						</li>
						<li class="mb-3">
							Delta State: Warri south
						</li>
					</ul>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
				<div class="w-100 h-100">
					<p class="text-muted">WHAT WE DO</p>
					<ul class="list-unstyled">
						<li class="mb-3">Laboratory Services</li>
						<li class="mb-3">Environmental Services</li>
						<li class="mb-3">Training</li>
						<li class="mb-3">Digital Solutions</li>
						<li class="mb-3">Waste Management</li>
						<li class="mb-3">Engineering</li>
					</ul>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
				<div class="w-100 h-100">
					<p class="text-muted">QUICK LINKS</p>
					<ul class="list-unstyled">
						<li class="mb-3"><a href="./" class="text-decoration-none text-black mb-2">Home</a></li>
						<li class="mb-3"><a href="./about" class="text-decoration-none text-black mb-2">About Us</a></li>
						<li class="mb-3"><a href="#" class="text-decoration-none text-black mb-2">What We do</a></li>
						<li class="mb-3"><a href="./contact" class="text-decoration-none text-black mb-2">Contact Us</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>
<?php require dirname(__DIR__) . '/Components/scripts.php'; ?>
