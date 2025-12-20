@extends('.layoutHome.layouts.app')
@section('content')

<!-- Hero Section -->
<section id="hero" class="hero section dark-background">
  @include('layoutHome.blocks.banner')
</section>
<!-- /Hero Section -->

<!-- Featured Services Section -->
<section id="featured-services" class="featured-services section">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up" data-aos-delay="100">
        <div class="icon flex-shrink-0"><i class="fa-solid fa-bolt"></i></div>
        <div>
          <h4 class="title">Giao Hàng Siêu Tốc</h4>
          <p class="description">Giao hàng trong vòng 24 giờ đối với các đơn hàng nội thành. Cam kết đưa sản phẩm đến tay khách hàng nhanh chóng và an toàn.</p>
          <a href="#" class="readmore stretched-link"><span>Tìm Hiểu Thêm</span><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up" data-aos-delay="200">
        <div class="icon flex-shrink-0"><i class="fa-solid fa-box"></i></div>
        <div>
          <h4 class="title">Đóng Gói Chuyên Nghiệp</h4>
          <p class="description">Dịch vụ đóng gói cẩn thận với các vật liệu chất lượng cao, đảm bảo hàng hóa không bị hư hại trong quá trình vận chuyển.</p>
          <a href="#" class="readmore stretched-link"><span>Tìm Hiểu Thêm</span><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>

      <div class="col-lg-4 col-md-6 service-item d-flex" data-aos="fade-up" data-aos-delay="300">
        <div class="icon flex-shrink-0"><i class="fa-solid fa-map-location-dot"></i></div>
        <div>
          <h4 class="title">Theo Dõi Thực Thời</h4>
          <p class="description">Ứng dụng di động và website cho phép bạn theo dõi đơn hàng của mình bất cứ lúc nào, ở bất cứ đâu. Cập nhật tức thì từ kho đến tay khách.</p>
          <a href="#" class="readmore stretched-link"><span>Tìm Hiểu Thêm</span><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /Featured Services Section -->

<!-- Services Section -->
<section id="services" class="services section">
  <div class="container section-title" data-aos="fade-up">
    <span>Dịch Vụ Của Chúng Tôi</span>
    <h2>Các Giải Pháp Giao Hàng Toàn Diện</h2>
    <p>Từ giao hàng trong nội thành đến vận chuyển quốc tế, chúng tôi cung cấp đầy đủ các dịch vụ để đáp ứng nhu cầu của bạn</p>
  </div>

  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-1.jpg" alt="" class="img-fluid">
          </div>
          <h3>Giao Hàng Nội Thành</h3>
          <p>Dịch vụ giao hàng nhanh trong nội thành các thành phố lớn. Cam kết giao trong cùng ngày hoặc hôm sau với giá cạnh tranh.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-2.jpg" alt="" class="img-fluid">
          </div>
          <h3><a href="#" class="stretched-link">Vận Chuyển Liên Tỉnh</a></h3>
          <p>Kết nối các tỉnh thành trên toàn quốc. Đội xe chuyên dụng, lộ trình tối ưu và thời gian giao hàng dự kiến chuẩn xác.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-3.jpg" alt="" class="img-fluid">
          </div>
          <h3><a href="#" class="stretched-link">Hàng Hóa Đặc Biệt</a></h3>
          <p>Vận chuyển hàng hóa đặc biệt như đồ dễ vỡ, hàng lạnh, hàng quý giá với các biện pháp bảo vệ tối đa.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-4.jpg" alt="" class="img-fluid">
          </div>
          <h3><a href="#" class="stretched-link">Kho Bãi & Lưu Trữ</a></h3>
          <p>Dịch vụ kho bãi hiện đại với hệ thống quản lý tự động, bảo mật cao và có thể quản lý hàng hóa theo yêu cầu.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-5.jpg" alt="" class="img-fluid">
          </div>
          <h3>Thu Gom Hàng Hóa</h3>
          <p>Dịch vụ thu gom hàng từ nhiều nơi, phân loại và gửi đến địa chỉ cuối cùng một cách hiệu quả.</p>
        </div>
      </div>

      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
        <div class="card">
          <div class="card-img">
            <img src="assets/img/service-6.jpg" alt="" class="img-fluid">
          </div>
          <h3><a href="#" class="stretched-link">Giải Pháp Logistics</a></h3>
          <p>Cung cấp giải pháp logistics tự định nghĩa, từ vận tải đến quản lý kho, phù hợp với nhu cầu kinh doanh của bạn.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /Services Section -->

<!-- About Section -->
<section id="about" class="about section">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-6 position-relative align-self-start order-lg-last order-first" data-aos="fade-up" data-aos-delay="200">
        <img src="assets/img/about.jpg" class="img-fluid" alt="">
        <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox pulsating-play-btn"></a>
      </div>

      <div class="col-lg-6 content order-last order-lg-first" data-aos="fade-up" data-aos-delay="100">
        <h3>Tại Sao Chọn Chúng Tôi?</h3>
        <p>
          Với hơn 10 năm kinh nghiệm trong lĩnh vực giao hàng và logistics, chúng tôi đã trở thành người bạn đáng tin cậy của hàng triệu khách hàng trên toàn quốc. Chúng tôi không chỉ giao hàng, mà còn xây dựng mối quan hệ lâu dài dựa trên sự uy tín và chất lượng dịch vụ.
        </p>
        <ul>
          <li>
            <i class="bi bi-diagram-3"></i>
            <div>
              <h5>Mạng Lưới Toàn Quốc</h5>
              <p>Kết nối 63 tỉnh thành với hơn 1000 điểm phát hành, đảm bảo giao hàng mọi nơi nhanh chóng và an toàn</p>
            </div>
          </li>
          <li>
            <i class="bi bi-fullscreen-exit"></i>
            <div>
              <h5>Công Nghệ Tiên Tiến</h5>
              <p>Hệ thống quản lý đơn hàng AI, theo dõi GPS thực thời gian, và ứng dụng di động dễ sử dụng</p>
            </div>
          </li>
          <li>
            <i class="bi bi-broadcast"></i>
            <div>
              <h5>Hỗ Trợ Khách Hàng 24/7</h5>
              <p>Đội ngũ chuyên viên hỗ trợ luôn sẵn sàng giúp đỡ qua điện thoại, email, chat hoặc mạng xã hội</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>
<!-- /About Section -->

<!-- Call To Action Section -->
<section id="call-to-action" class="call-to-action section dark-background">
  <img src="assets/img/cta-bg.jpg" alt="">
  <div class="container">
    <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
      <div class="col-xl-10">
        <div class="text-center">
          <h3>Bắt Đầu Giao Hàng Ngay Hôm Nay</h3>
          <p>Đăng ký tài khoản miễn phí và nhận ưu đãi 20% cho 10 đơn hàng đầu tiên. Không cần hợp đồng dài hạn, chỉ cần là hành động nhanh chóng và an toàn.</p>
          <a class="cta-btn" href="{{ url('/register') }}">Đăng Ký Ngay</a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /Call To Action Section -->

<!-- Features Section -->
<section id="features" class="features section">
  <div class="container section-title" data-aos="fade-up">
    <span>Tính Năng Nổi Bật</span>
    <h2>Những Lợi Thế Cạnh Tranh</h2>
    <p>Các tính năng được thiết kế để giúp bạn quản lý giao hàng một cách hiệu quả nhất</p>
  </div>

  <div class="container">
    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
        <img src="assets/img/features-1.jpg" class="img-fluid" alt="">
      </div>
      <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
        <h3>Quản Lý Đơn Hàng Tập Trung</h3>
        <p class="fst-italic">
          Tất cả đơn hàng của bạn được quản lý trong một nền tảng duy nhất, từ tạo đơn đến giao hàng và lập hóa đơn.
        </p>
        <ul>
          <li><i class="bi bi-check"></i><span>Tạo, chỉnh sửa và theo dõi đơn hàng trong vài giây</span></li>
          <li><i class="bi bi-check"></i><span>Tự động hóa quy trình xử lý đơn hàng</span></li>
          <li><i class="bi bi-check"></i><span>Lập báo cáo chi tiết về doanh thu và chi phí giao hàng</span></li>
        </ul>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
        <img src="assets/img/features-2.jpg" class="img-fluid" alt="">
      </div>
      <div class="col-md-7 order-2 order-md-1" data-aos="fade-up" data-aos-delay="200">
        <h3>Theo Dõi Thực Thời Gian</h3>
        <p class="fst-italic">
          Biết chính xác vị trí hàng hóa của bạn bất cứ lúc nào, từ kho đến tay khách hàng cuối cùng.
        </p>
        <p>
          Sử dụng công nghệ GPS tiên tiến, khách hàng của bạn có thể theo dõi đơn hàng của họ thông qua ứng dụng di động hoặc website. Cập nhật tức thì khi hàng hóa được nhân viên giao nhận, chuyển giao hoặc giao thành công.
        </p>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out">
        <img src="assets/img/features-3.jpg" class="img-fluid" alt="">
      </div>
      <div class="col-md-7" data-aos="fade-up">
        <h3>Giá Cước Minh Bạch</h3>
        <p>Không có chi phí ẩn, tất cả các khoản phí được hiển thị rõ ràng trước khi xác nhận đơn hàng. Giá cước được tính toán dựa trên khoảng cách, trọng lượng và loại hàng hóa.</p>
        <ul>
          <li><i class="bi bi-check"></i><span>Giá cước cạnh tranh trên thị trường</span></li>
          <li><i class="bi bi-check"></i><span>Chiết khấu cho khách hàng doanh nghiệp</span></li>
          <li><i class="bi bi-check"></i><span>Không có phí thêm bất ngờ</span></li>
        </ul>
      </div>
    </div>

    <div class="row gy-4 align-items-center features-item">
      <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out">
        <img src="assets/img/features-4.jpg" class="img-fluid" alt="">
      </div>
      <div class="col-md-7 order-2 order-md-1" data-aos="fade-up">
        <h3>Bảo Hiểm Hàng Hóa Đầy Đủ</h3>
        <p class="fst-italic">
          Tất cả các đơn hàng được bảo hiểm tự động, bảo vệ hàng hóa của bạn trong quá trình vận chuyển.
        </p>
        <p>
          Nếu hàng hóa bị hư hại, thất lạc hoặc mất, chúng tôi sẽ bồi thường 100% giá trị hàng hóa (theo giá khai báo). Không cần phải lo lắng về rủi ro, chúng tôi sẽ đảm bảo cho bạn.
        </p>
      </div>
    </div>
  </div>
</section>
<!-- /Features Section -->

<!-- Testimonials Section -->
<section id="testimonials" class="testimonials section dark-background">
  <img src="assets/img/testimonials-bg.jpg" class="testimonials-bg" alt="">

  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="section-title mb-4 text-white">
      <span>Đánh Giá Từ Khách Hàng</span>
      <h2>Những Lời Khen Từ Những Người Dùng Thực</h2>
    </div>

    <div class="swiper init-swiper">
      <script type="application/json" class="swiper-config">
        {
          "loop": true,
          "speed": 600,
          "autoplay": {
            "delay": 5000
          },
          "slidesPerView": "auto",
          "pagination": {
            "el": ".swiper-pagination",
            "type": "bullets",
            "clickable": true
          }
        }
      </script>
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <div class="testimonial-item">
            <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
            <h3>Nguyễn Văn A</h3>
            <h4>Chủ Cửa Hàng Online</h4>
            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>Tôi đã sử dụng dịch vụ của các công ty khác, nhưng chắc chắn công ty này là tốt nhất. Giá cước rẻ, giao hàng nhanh và khách hàng của tôi rất hài lòng.</span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="testimonial-item">
            <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
            <h3>Trần Thị B</h3>
            <h4>Kinh Doanh Bán Lẻ</h4>
            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>Hệ thống theo dõi đơn hàng của họ rất hữu ích. Tôi có thể biết được chính xác vị trí hàng hóa bất cứ lúc nào. Hỗ trợ khách hàng cũng rất thân thiện.</span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="testimonial-item">
            <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
            <h3>Phạm Văn C</h3>
            <h4>Nhà Cung Cấp Sản Phẩm</h4>
            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>Giá cước cũng rất cạnh tranh so với các công ty khác. Tôi sẽ tiếp tục sử dụng họ cho tất cả các đơn hàng của mình.</span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="testimonial-item">
            <img src="assets/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
            <h3>Lê Thị D</h3>
            <h4>Quản Lý Kho Hàng</h4>
            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>Dịch vụ kho bãi của họ rất chuyên nghiệp. Hàng hóa được quản lý tốt, bảo mật an toàn. Nhân viên cũng rất lịch sự và có tác phong tốt.</span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="testimonial-item">
            <img src="assets/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
            <h3>Trịnh Văn E</h3>
            <h4>Doanh Nhân Xuất Khẩu</h4>
            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
            </div>
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>API tích hợp của họ rất dễ sử dụng. Tôi đã kết nối hệ thống của mình trong vài ngày. Giúp tôi tiết kiệm được rất nhiều thời gian.</span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>
</section>
<!-- /Testimonials Section -->

<!-- FAQ Section -->
<section id="faq" class="faq section">
  <div class="container section-title" data-aos="fade-up">
    <span>Câu Hỏi Thường Gặp</span>
    <h2>Các Câu Hỏi Thường Gặp</h2>
    <p>Tìm câu trả lời cho những câu hỏi phổ biến về dịch vụ giao hàng của chúng tôi</p>
  </div>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="faq-container">
          <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="200">
            <i class="faq-icon bi bi-question-circle"></i>
            <h3>Thời gian giao hàng là bao lâu?</h3>
            <div class="faq-content">
              <p>Thời gian giao hàng tùy thuộc vào loại dịch vụ bạn chọn. Giao hàng nội thành thường từ 24-48 giờ, vận chuyển liên tỉnh từ 2-5 ngày tùy khoảng cách. Bạn sẽ nhận được ước tính chính xác trước khi xác nhận đơn hàng.</p>
            </div>
            <i class="faq-toggle bi bi-chevron-right"></i>
          </div>

          <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
            <i class="faq-icon bi bi-question-circle"></i>
            <h3>Tôi có thể theo dõi đơn hàng của mình không?</h3>
            <div class="faq-content">
              <p>Có, bạn có thể theo dõi đơn hàng thông qua ứng dụng di động hoặc website của chúng tôi. Hệ thống GPS sẽ cập nhật vị trí của hàng hóa theo thời gian thực. Bạn cũng sẽ nhận được thông báo qua SMS hoặc email khi đơn hàng được giao nhận, chuyển giao hoặc giao thành công.</p>
            </div>
            <i class="faq-toggle bi bi-chevron-right"></i>
          </div>

          <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
            <i class="faq-icon bi bi-question-circle"></i>
            <h3>Nếu hàng hóa của tôi bị hư hại thì sao?</h3>
            <div class="faq-content">
              <p>Tất cả các đơn hàng được bảo hiểm tự động. Nếu hàng hóa bị hư hại, thất lạc hoặc mất trong quá trình vận chuyển, chúng tôi sẽ bồi thường 100% giá trị hàng hóa theo giá khai báo của bạn. Vui lòng liên hệ với chúng tôi ngay sau khi nhận hàng để báo cáo.</p>
            </div>
            <i class="faq-toggle bi bi-chevron-right"></i>
          </div>

          <div class="faq-item" data-aos="fade-up" data-aos-delay="500">
            <i class="faq-icon bi bi-question-circle"></i>
            <h3>Giá cước của bạn có cạnh tranh không?</h3>
            <div class="faq-content">
              <p>Giá cước của chúng tôi rất cạnh tranh và minh bạch. Không có chi phí ẩn, tất cả các khoản phí được hiển thị rõ ràng trước khi bạn xác nhận đơn hàng. Chúng tôi cũng cung cấp chiết khấu cho khách hàng doanh nghiệp có lượng đơn hàng lớn.</p>
            </div>
            <i class="faq-toggle bi bi-chevron-right"></i>
          </div>

          <div class="faq-item" data-aos="fade-up" data-aos-delay="600">
            <i class="faq-icon bi bi-question-circle"></i>
            <h3>Làm thế nào để đăng ký tài khoản?</h3>
            <div class="faq-content">
              <p>Bạn có thể đăng ký tài khoản miễn phí trên website hoặc ứng dụng di động của chúng tôi. Quá trình đăng ký rất đơn giản, chỉ cần cung cấp thông tin cơ bản như tên, email, số điện thoại và địa chỉ. Sau khi đăng ký, bạn sẽ nhận được ưu đãi 20% cho 10 đơn hàng đầu tiên.</p>
            </div>
            <i class="faq-toggle bi bi-chevron-right"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /FAQ Section -->

@endsection