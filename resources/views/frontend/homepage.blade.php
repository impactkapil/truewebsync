@extends('frontend.layouts.app')

@section('title', 'Home')

@section('content')
    <section class="bg-success-subtle py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h1 class="display-6">Welcome to TrueWebSync</h1>
                    <h2 class="display-5 fw-bolder">Expand your reach by syncing inventory across <span class="text-success">Shopify</span> and Custom stores</h2>
                    <p>
                        Real-time sync inventory, product details, orders, and customers across multiple stores.
                    </p>
                    <button class="btn btn-success rounded-0 btn-lg">Try for Free</button>
                    <a class="link-dark text-decoration-none" href="#">Cancel Any Time</a>
                </div>
                <div class="col-md-5">
                    <img src="https://cdn.truewebpro.com/image1.png"
                         alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center display-5 fw-bold">Trusted by more than
                        <span class="text-success fw-bold">100+</span>
                        Shopify e-commerce stores</h2>
                </div>
            </div>
            <div class="row mt-5 g-4 bimage row-cols-2 row-cols-md-4 row-cols-lg-6 align-items-center">
                <div class="col">
                    <div class="image">
                        <img class="img-fluid" src="https://mcrvapedistro.co.uk/cdn/shop/files/logo.png?v=1614756228" alt="">
                    </div>
                </div>
                <div class="col">
                    <div class="image bg-dark p-2">
                        <img class="img-fluid" src="https://simbavapes.co.uk/cdn/shop/files/SIMBA-VAPES.png?v=1684273740&width=240" alt="">
                    </div>
                </div>
                <div class="col">
                    <div class="image">
                        <img class="img-fluid" src="https://www.simbavapeswholesale.co.uk/cdn/shop/files/Untitled-1_03_350x_c03e4163-89f6-44ae-ac71-9303b7e19446_135x@2x.webp?v=1683824610" alt="">
                    </div>
                </div>
                <div class="col">
                    <div class="image">
                        <img class="img-fluid" src="https://www.goldmaryvape.com/cdn/shop/files/gold_mary_logo.png?v=1691607180&width=180" alt="">
                    </div>
                </div>
                <div class="col">
                    <div class="image bg-dark p-2">
                        <img class="img-fluid" src="https://www.mcrsnackdistro.co.uk/cdn/shop/files/crystal.png?v=1691002864&width=400" alt="">
                    </div>
                </div>
                <div class="col">
                    <div class="image">
                        <img class="img-fluid" src="https://vapeukwholesale.co.uk/cdn/shop/files/VAPE_UK_WHOLSALE_a730ffdb-0615-42d0-9b51-46b687032a41_200x@2x.png?v=1650830692" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Hero Section -->
{{--<section class="hero sec-padding">--}}
{{--    <div class="container">--}}
{{--        <div class="hero-text text-center">--}}
{{--            <h4>Welcome to MShopify Store</h4>--}}
{{--            <h1>Manage your <span class="text-highlighted-primary">Shopify Inventory</span> effortlessly<br /> with our--}}
{{--                <span class="text-highlighted-tertiary">Comprehensive Packages</span>.--}}
{{--            </h1>--}}
{{--            <a href="{{ route('packages') }}" class="btn btn-main">Explore Packages<i--}}
{{--                    class="fa-solid fa-arrow-right-long"></i></a>--}}
{{--            <div class="rating">--}}
{{--                Rated 5.0--}}
{{--                <div class="rating-stars">--}}
{{--                    <i class="fa-solid fa-star"></i>--}}
{{--                    <i class="fa-solid fa-star"></i>--}}
{{--                    <i class="fa-solid fa-star"></i>--}}
{{--                    <i class="fa-solid fa-star"></i>--}}
{{--                    <i class="fa-solid fa-star"></i>--}}
{{--                </div>--}}
{{--                on Shopify--}}
{{--                <i class="fa-brands fa-shopify"></i>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</section>--}}
<!-- Hero Section -->
<section class="bg-dark py-5 expo">
    <div class="container">
        <h2 class="text-center text-white display-5">Effortlessly manage inventory, products and orders</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 justify-content-center g-5 mt-4">
            <div class="col">
                <div class="text-center shadow-lg">
                    <i class="iconify fs-1" data-icon="lsicon:inventory-outline"></i>
                    <h3>Never oversell again</h3>
                    <p>Real-time inventory sync and updates on imported products</p>
                </div>
            </div>
            <div class="col">
                <div class="text-center shadow-lg">
                    <i class="iconify fs-1" data-icon="line-md:list"></i>
                    <h3>Customise what you sync</h3>
                    <p>Sync product information including metafields, images, prices, tags, descriptions, and more.</p>
                </div>
            </div>
            <div class="col">
                <div class="text-center shadow-lg">
                    <i class="iconify fs-1" data-icon="material-symbols:trolley-outline-rounded"></i>
                    <h3>Automate your orders</h3>
                    <p>Push orders to your supplier for fulfilment. TrueWebSync will update fulfilment and tracking</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="display-5 text-center">Easy, flexible and just works</h2>
        <p class="text-center">Whether you’re a supplier, retailer or are looking to expand through more of your own stores, TrueWebSync helps you spend more time on the things you love</p>
        <div class="row mt-4 align-items-center">
            <div class="col-md-6 text-center">
                <img src="https://sellerchamp.com/wp-content/uploads/2021/06/RePricer-2-1024x1020.png"
                     alt="TrueWebSync" class="img-fluid" width="360">
            </div>
            <div class="col-md-6">
                <h3 class="text-uppercase h6">Sync Inventory seamlessly</h3>
                <h4 class="h2 fw-bold">Inventory synced on the stores your customer shops on</h4>
                <p>Your inventory is updated in real-time to all connected Shopify and Custom stores.</p>
                <ul>
                    <li>Map products by inventory-only if products already exist across stores</li>
                    <li>Inventory updates across connected stores within seconds</li>
                    <li>Import thousands of products within minutes to your store</li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-success-subtle">
    <div class="container">
        <div class="row mt-4 align-items-center">
            <div class="col-md-6">
                <h2 class="text-uppercase h6">Sync product attributes</h2>
                <h3 class="h2 fw-bold">Update product information once for all of your connected stores</h3>
                <p>Have new variants or images to add to your product? Make that update in your source store and TrueWebSync will sync it across to all connected stores.</p>
                <ul>
                    <li>Map products by inventory-only if products already exist across stores</li>
                    <li>Inventory updates across connected stores within seconds</li>
                    <li>Import thousands of products within minutes to your store</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <img src="https://cdn.prod.website-files.com/5fb6eb0390b37ff6d00f7b67/65bc7994b882911ecf0ca2be_61383e1397e84062ce0aec6b_customsync_vue3_2024_updated-p-800.png"
                     alt="TrueWebSync" class="img-fluid">
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row mt-4 align-items-center">
            <div class="col-md-6 text-center">
                <img src="https://cdn.prod.website-files.com/5fb6eb0390b37ff6d00f7b67/65bc7b5956c1c2406395f764_61383dfcca29e92500aeb11d_orders_vue3_2024_updated-p-800.png"
                     alt="TrueWebSync" class="img-fluid">
            </div>
            <div class="col-md-6">
                <h2 class="text-uppercase h6">Push orders for easy fulfillment</h2>
                <h3 class="h2 fw-bold">Orders forwarded directly to the store that fulfills and ships</h3>
                <p>Orders that are made on your expansion or partner stores are forwarded back to your source store to fulfill centrally</p>
                <ul>
                    <li>Map products by inventory-only if products already exist across stores</li>
                    <li>Inventory updates across connected stores within seconds</li>
                    <li>Import thousands of products within minutes to your store</li>
                </ul>
            </div>

        </div>
    </div>
</section>
<section class="features bg-dark py-5">
    <div class="container">
            <h2 class="text-center text-white display-5">Why Us?</h2>
        <div class="row mt-5">
            <div class="col-lg-4">
                <div class="feature-container" data-aos="fade-up">
                    <i class="fas fa-cogs fa-3x mb-3"></i>
                    <h3>Easy Integration</h3>
                    <p>Seamlessly integrate with your Shopify store to manage inventory efficiently.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-container" data-aos="fade-up" data-aos-delay="50">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <h3>Real-Time Analytics</h3>
                    <p>Monitor your sales and inventory with real-time data and actionable insights.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-container" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-headset fa-3x mb-3"></i>
                    <h3>24/7 Support</h3>
                    <p>Get round-the-clock support to ensure your business runs smoothly.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="contact py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="contact-left" data-aos="fade-right">
                    <div class="heading-container text-left">
                        <h2 class="display-5 fw-bold">Build, manage and grow <span class="text-success">Your Business</span>
                            Smooothly.</h2>
                    </div>
                    <form class="card card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" />
                        </div>
                        <div class="mb-3">
                            <label for="workEmail" class="form-label">Work Email</label>
                            <input type="email" class="form-control" id="workEmail">
                        </div>
                        <div class="mb-3">
                            <label for="companySize" class="form-label">Company Size</label>
                            <select id="companySize" class="form-select">
                                <option selected>Please Select</option>
                                <option value="0-10">0-10</option>
                                <option value="10-100">10-100</option>
                                <option value="100-1000">100-1000</option>
                                <option value="1000+">1000+</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-main">Submit<i
                                class="fa-solid fa-arrow-right-long"></i></button>
                    </form>
                </div>
            </div>
            <div class="offset-lg-1 col-lg-5">
                <div class="contact-right" data-aos="fade-left">
                    <div></div>
                    <img src="{{ asset('images/bags.jpg') }}" alt="Bags" />
                </div>
            </div>
        </div>
    </div>
</section>
    <section id="pricing" class="prices py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold display-5" data-aos="zoom-in">Donation for community</h2>
            <h3 class="h5 text-center text-uppercase fw-bold">Our Pricing</h3>
            <div class="row mt-3 row-cols-1 row-cols-sm-2 row-cols-md-3 g-5">
                <div class="col">
                    <div class="card">
                        <div class="card-header text-bg-info">
                            <h2 class="text-center">Starter</h2>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Android app
                                    <i class="iconify fs-3" data-icon="carbon:application-mobile"></i>
                                    <i class="iconify text-success fs-3" data-icon="devicon:android"></i>
                                </li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Convert any website</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    In-app browser</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Customizable design</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Real device testing</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Push notifications</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Monetization features</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Team collaboration</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Trueweb Branding</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-info rounded-0 fw-bold">Start Now</button>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header text-bg-info">
                            <h2 class="text-center">Pro</h2>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Android & IOS app
                                    <i class="iconify fs-3" data-icon="carbon:application-mobile"></i>
                                    <i class="iconify text-success fs-3" data-icon="devicon:android"></i>
                                    <i class="iconify fs-3" data-icon="fa:apple"></i>
                                </li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Convert any website</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    In-app browser</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Customizable design</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Real device testing</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Push notifications</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Monetization features</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Team collaboration</li>
                                <li class="list-group-item">
                                    <i class="iconify text-danger fs-3" data-icon="charm:circle-cross"></i>
                                    No Trueweb Branding</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-info rounded-0 fw-bold">Start Now</button>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header text-bg-info">
                            <h2 class="text-center">Premium</h2>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Android & IOS app
                                    <i class="iconify fs-3" data-icon="carbon:application-mobile"></i>
                                    <i class="iconify fs-3" data-icon="devicon:android"></i>
                                    <i class="iconify fs-3" data-icon="fa:apple"></i>
                                </li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Convert any website</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    In-app browser</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Customizable design</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Real device testing</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Push notifications</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Monetization features</li>
                                <li class="list-group-item">
                                    <i class="iconify text-success fs-3" data-icon="uil:check-circle"></i>
                                    Team collaboration</li>
                                <li class="list-group-item">
                                    <i class="iconify text-danger fs-3" data-icon="charm:circle-cross"></i>
                                    No Trueweb Branding</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-info rounded-0 fw-bold">Start Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<section class="compare bg-success-subtle py-5">
    <div class="container">
        <div class="heading-container my-3 d-flex flex-wrap align-items-start justify-content-between" data-aos="fade-down">
            <h2 class="display-5 fw-bold">Compare for Yourself</h2>
            <a href="{{ route('packages') }}" class="btn btn-main btn-main-light">Explore Packages<i
                    class="fa-solid fa-arrow-right-long"></i></a>
        </div>
        <div class="table-wrapper table-responsive" data-aos="fade-up">
            <table class="table table-responsive-lg table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Features</th>
                        <th>TrueWebSync</th>
                        <th>Shopify</th>
                        <th>Magento</th>
                        <th>WooCommerce</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Tailored Enterprise Solutions</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                    </tr>
                    <tr>
                        <th>Diverse Business Models</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                    <tr>
                        <th>B2B + B2C Capabilities</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                    <tr>
                        <th>Mobile Apps Builder + PWA</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                    <tr>
                        <th>Multi Store Managed from Central Admin</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                    <tr>
                        <th>Pre-Integrated Solutions</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                    <tr>
                        <th>Builtin Tax Engine (Automated GST)</th>
                        <td><i class="fa-solid fa-check"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                        <td><i class="fa-solid fa-xmark"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="testimonials py-5">
    <div class="container">
        <div class="heading-container">
            <h2 class="display-5 fw-bold my-4">What our Customers say</h2>
        </div>
        <div class="swiper swiper-testimonials">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-container">
                        <i class="fa-solid fa-quote-right"></i>
                        <img src="{{ asset('images/user.jpg') }}" alt="Customer" />
                        <h3>Abigail Clarke</h3>
                        <h5>Lorem ipsum dolor sit.</h5>
                        <p>Nunc pellentesque dui accumsan quam tempor molestie. Nullam eros orci, ullamcorper id nisl non,
                            mattis fringilla erat. Aenean et odio dui. Donec feugiat, nunc lobortis volutpat auctor, odio
                            metus rutrum arcu, ut consectetur magna neque sed nibh.</p>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
@endsection
