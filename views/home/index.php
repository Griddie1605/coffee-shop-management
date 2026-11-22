<!-- 1. Hero toàn màn hình -->
<section class="landing-hero d-flex align-items-center">
    <div class="container px-lg-5 landing-hero-content">
        <p class="landing-kicker mb-2">Cafe &amp; Restaurant Est. 2035</p>
        <h1 class="landing-hero-title mb-4">
            Your Go-to Spot for<br>
            Delicious Eats &amp; Coffee
        </h1>
        <div class="d-flex flex-wrap gap-3">
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-landing-outline">Our Menu</a>
        </div>
    </div>
</section>

<!-- 2. Khối Eat. (Chữ trái - Ảnh phải - thu gọn hơn Enjoy) -->
<section id="eat-section" class="row g-0 align-items-stretch split-band split-band--compact">
    <div class="col-lg-6 d-flex align-items-center split-text-col">
        <div class="split-text-inner">
            <h2 class="split-heading mb-2">Eat.</h2>
            <p class="split-subtitle mb-3">Breakfast, Lunch and Artisanal Pastries</p>
            <p class="split-desc mb-4">
                Freshly baked pastries, savory morning plates, and comforting bites prepared daily to pair with your favorite brew.
            </p>
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-split-dark">See More</a>
        </div>
    </div>
    <div class="col-lg-6 split-media-col">
        <img
            src="https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=1200&q=80"
            alt="Breakfast and artisanal toast"
            class="split-img"
        >
    </div>
</section>

<!-- 3. Khối Drink. (Ảnh trái - Chữ phải - thu gọn hơn Enjoy) -->
<section class="row g-0 align-items-stretch split-band split-band--compact">
    <div class="col-lg-6 order-2 order-lg-1 split-media-col">
        <img
            src="https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=1200&q=80"
            alt="Pouring fresh latte"
            class="split-img"
        >
    </div>
    <div class="col-lg-6 order-1 order-lg-2 d-flex align-items-center split-text-col">
        <div class="split-text-inner">
            <h2 class="split-heading mb-2">Drink.</h2>
            <p class="split-subtitle mb-3">The Freshest Cup in Town</p>
            <p class="split-desc mb-4">
                From rich espresso and smooth latte art to refreshing fruit teas and matcha, every cup is crafted with care.
            </p>
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-split-dark">See More</a>
        </div>
    </div>
</section>

<!-- 4. Khối Enjoy. (Chữ trái - Ảnh phải) -->
<section class="row g-0 align-items-stretch split-band">
    <div class="col-lg-6 d-flex align-items-center split-text-col">
        <div class="split-text-inner">
            <h2 class="split-heading mb-2">Enjoy.</h2>
            <p class="split-subtitle mb-3">Make Yourself at Home</p>
            <p class="split-desc mb-4">
                A warm, welcoming corner to slow down, catch up with friends, or enjoy a quiet coffee break at your own pace.
            </p>
            <a href="#visit-section" class="btn btn-split-dark">Visit Us</a>
        </div>
    </div>
    <div class="col-lg-6 split-media-col">
        <img
            src="https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=1200&q=80"
            alt="Friends enjoying coffee and pastries"
            class="split-img"
        >
    </div>
</section>

<!-- 5. Khu vực Địa chỉ & Giờ mở cửa -->
<section id="visit-section" class="visit-band py-5">
    <div class="container py-4 text-center">
        <h2 class="visit-main-title mb-5">Drop By for a Bite.</h2>

        <div class="row justify-content-center align-items-center g-4">
            <div class="col-md-5">
                <h3 class="visit-col-title mb-3">Address</h3>
                <p class="visit-col-text mb-0">
                    500 Terry Francine St.<br>
                    San Francisco, CA 94158
                </p>
            </div>

            <div class="col-md-auto d-none d-md-block">
                <div class="visit-divider"></div>
            </div>

            <div class="col-md-5">
                <h3 class="visit-col-title mb-3">Opening Hours</h3>
                <p class="visit-col-text mb-0">
                    Mon - Fri: 9am - 6pm<br>
                    Saturday: 10am - 2pm<br>
                    Sunday: Closed
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 6. Dải 5 ảnh gallery ngang sát nhau -->
<section class="gallery-strip">
    <div class="row row-cols-5 g-0">
        <div class="col gallery-cell">
            <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80" alt="Latte art cup">
        </div>
        <div class="col gallery-cell">
            <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80" alt="Cafe interior seating">
        </div>
        <div class="col gallery-cell">
            <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80" alt="Fresh bowl">
        </div>
        <div class="col gallery-cell">
            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80" alt="Wooden cafe tables">
        </div>
        <div class="col gallery-cell">
            <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80" alt="Fresh baked croissant">
        </div>
    </div>
</section>

<!-- 5 khoảng trắng trước footer -->
<div class="gallery-spacer" aria-hidden="true"></div>
<div class="gallery-spacer" aria-hidden="true"></div>
<div class="gallery-spacer" aria-hidden="true"></div>
<div class="gallery-spacer" aria-hidden="true"></div>
<div class="gallery-spacer" aria-hidden="true"></div>