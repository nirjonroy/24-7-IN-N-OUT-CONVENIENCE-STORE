      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <a href="{{ route('dashboard') }}" class="brand-link">
            <img
              src="{{ asset('admin-assets/assets/img/AdminLTELogo.png') }}"
              alt="Admin Logo"
              class="brand-image opacity-75 shadow"
            />
            <span class="brand-text fw-light">Admin Panel</span>
          </a>
        </div>
        <!--end::Sidebar Brand-->

        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.siteinfo.edit') }}" class="nav-link {{ request()->routeIs('admin.siteinfo.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-gear-fill"></i>
                  <p>Site Info</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.sliders.index') }}" class="nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-images"></i>
                  <p>Sliders</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.about.edit') }}" class="nav-link {{ request()->routeIs('admin.about.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-info-circle-fill"></i>
                  <p>About</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.contact-info.edit') }}" class="nav-link {{ request()->routeIs('admin.contact-info.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-geo-alt-fill"></i>
                  <p>Contact Info</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-envelope-fill"></i>
                  <p>Contacts</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.businesses.index') }}" class="nav-link {{ request()->routeIs('admin.businesses.*') || request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-shop"></i>
                  <p>Business</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-file-earmark-text"></i>
                  <p>Pages</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.media.index') }}" class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-folder2-open"></i>
                  <p>Media Library</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.catalog.categories.index') }}" class="nav-link {{ request()->routeIs('admin.catalog.categories.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-tags"></i>
                  <p>Catalog Categories</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.catalog.items.index') }}" class="nav-link {{ request()->routeIs('admin.catalog.items.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-box-seam"></i>
                  <p>Catalog Items</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.faq.categories.index') }}" class="nav-link {{ request()->routeIs('admin.faq.categories.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-question-circle"></i>
                  <p>FAQ Categories</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.faq.questions.index') }}" class="nav-link {{ request()->routeIs('admin.faq.questions.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-chat-left-text"></i>
                  <p>FAQs</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.gallery.categories.index') }}" class="nav-link {{ request()->routeIs('admin.gallery.categories.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-collection"></i>
                  <p>Gallery Categories</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.gallery.items.index') }}" class="nav-link {{ request()->routeIs('admin.gallery.items.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-image"></i>
                  <p>Gallery Items</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.phone-repair.services.index') }}" class="nav-link {{ request()->routeIs('admin.phone-repair.services.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-tools"></i>
                  <p>Repair Services</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.phone-repair.brands.index') }}" class="nav-link {{ request()->routeIs('admin.phone-repair.brands.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-phone"></i>
                  <p>Device Brands</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('admin.phone-repair.models.index') }}" class="nav-link {{ request()->routeIs('admin.phone-repair.models.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-phone-fill"></i>
                  <p>Device Models</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
