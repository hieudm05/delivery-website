<!DOCTYPE html>
<html lang="en">

<head>
    @include('customer.blocks.head')
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    @include('customer.blocks.header')
  </header>

  <main class="main">

    @yield('content')

  </main>

  <footer id="footer" class="footer dark-background">
    @include('customer.blocks.footer')
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

    @include('customer.blocks.js')

</body>

</html>