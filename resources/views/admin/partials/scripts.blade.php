    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('admin-assets/js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Choose Media</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="input-group mb-3">
              <input type="search" class="form-control" id="mediaPickerSearch" placeholder="Search media">
              <button class="btn btn-outline-secondary" type="button" id="mediaPickerSearchBtn">Search</button>
            </div>
            <div id="mediaPickerResults"></div>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        let activePicker = null;
        let activeGallery = null;
        const modalElement = document.getElementById('mediaPickerModal');
        const resultsElement = document.getElementById('mediaPickerResults');
        const searchInput = document.getElementById('mediaPickerSearch');
        const searchButton = document.getElementById('mediaPickerSearchBtn');
        const modal = modalElement ? new bootstrap.Modal(modalElement) : null;

        function loadMedia(search = '') {
          if (!activePicker || !resultsElement) return;
          const endpoint = new URL(activePicker.dataset.pickerEndpoint, window.location.origin);
          if (search) endpoint.searchParams.set('search', search);
          fetch(endpoint.toString(), { headers: { 'Accept': 'application/json' } })
            .then((response) => response.json())
            .then((data) => { resultsElement.innerHTML = data.html; });
        }

        document.querySelectorAll('[data-media-picker]').forEach(function (picker) {
          const input = picker.querySelector('[data-media-picker-input]');
          const preview = picker.querySelector('[data-media-picker-preview]');
          const empty = picker.querySelector('[data-media-picker-empty]');

          picker.querySelector('[data-media-picker-open]')?.addEventListener('click', function () {
            activePicker = picker;
            activeGallery = null;
            if (searchInput) searchInput.value = '';
            loadMedia();
            modal?.show();
          });

          picker.querySelector('[data-media-picker-remove]')?.addEventListener('click', function () {
            input.value = '';
            preview.src = '';
            preview.classList.add('d-none');
            empty.classList.remove('d-none');
          });
        });

        function syncGalleryInput(gallery) {
          const ids = Array.from(gallery.querySelectorAll('[data-media-gallery-item]')).map((item) => item.dataset.mediaId);
          gallery.querySelector('[data-media-gallery-input]').value = ids.join(',');
        }

        document.querySelectorAll('[data-media-gallery]').forEach(function (gallery) {
          gallery.querySelector('[data-media-gallery-open]')?.addEventListener('click', function () {
            activeGallery = gallery;
            activePicker = null;
            if (searchInput) searchInput.value = '';
            const endpoint = new URL(gallery.dataset.pickerEndpoint, window.location.origin);
            fetch(endpoint.toString(), { headers: { 'Accept': 'application/json' } })
              .then((response) => response.json())
              .then((data) => { resultsElement.innerHTML = data.html; });
            modal?.show();
          });

          gallery.addEventListener('click', function (event) {
            const remove = event.target.closest('[data-media-gallery-remove]');
            if (!remove) return;
            remove.closest('[data-media-gallery-item]').remove();
            syncGalleryInput(gallery);
          });
        });

        searchButton?.addEventListener('click', function () {
          loadMedia(searchInput.value);
        });

        searchInput?.addEventListener('keydown', function (event) {
          if (event.key === 'Enter') {
            event.preventDefault();
            loadMedia(searchInput.value);
          }
        });

        resultsElement?.addEventListener('click', function (event) {
          const button = event.target.closest('.media-picker-select');
          if (!button) return;
          if (activeGallery) {
            const list = activeGallery.querySelector('[data-media-gallery-list]');
            if (!list.querySelector(`[data-media-id="${button.dataset.mediaId}"]`)) {
              const wrapper = document.createElement('div');
              wrapper.className = 'position-relative border rounded p-1';
              wrapper.dataset.mediaGalleryItem = '';
              wrapper.dataset.mediaId = button.dataset.mediaId;
              wrapper.innerHTML = `<img src="${button.dataset.mediaUrl}" alt="" style="width:90px;height:70px;object-fit:cover" loading="lazy"><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 py-0 px-1" data-media-gallery-remove>&times;</button>`;
              list.appendChild(wrapper);
              syncGalleryInput(activeGallery);
            }
            activeGallery = null;
            modal?.hide();
            return;
          }
          if (!activePicker) return;
          const input = activePicker.querySelector('[data-media-picker-input]');
          const preview = activePicker.querySelector('[data-media-picker-preview]');
          const empty = activePicker.querySelector('[data-media-picker-empty]');
          input.value = button.dataset.mediaId;
          preview.src = button.dataset.mediaUrl;
          preview.classList.remove('d-none');
          empty.classList.add('d-none');
          activePicker = null;
          modal?.hide();
        });
      });
    </script>
    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->
    <script>
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    <!-- ChartJS -->
    <script>
      // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
      // IT'S ALL JUST JUNK FOR DEMO
      // ++++++++++++++++++++++++++++++++++++++++++

      const sales_chart_options = {
        series: [
          {
            name: 'Digital Goods',
            data: [28, 48, 40, 19, 86, 27, 90],
          },
          {
            name: 'Electronics',
            data: [65, 59, 80, 81, 56, 55, 40],
          },
        ],
        chart: {
          height: 300,
          type: 'area',
          toolbar: {
            show: false,
          },
        },
        legend: {
          show: false,
        },
        colors: ['#0d6efd', '#20c997'],
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          type: 'datetime',
          categories: [
            '2023-01-01',
            '2023-02-01',
            '2023-03-01',
            '2023-04-01',
            '2023-05-01',
            '2023-06-01',
            '2023-07-01',
          ],
        },
        tooltip: {
          x: {
            format: 'MMMM yyyy',
          },
        },
      };

      const sales_chart = new ApexCharts(
        document.querySelector('#revenue-chart'),
        sales_chart_options,
      );
      sales_chart.render();
    </script>
    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>
    <!-- jsvectormap -->
    <script>
      // World map by jsVectorMap
      new jsVectorMap({
        selector: '#world-map',
        map: 'world',
      });

      // Sparkline charts
      const option_sparkline1 = {
        series: [
          {
            data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
      sparkline1.render();

      const option_sparkline2 = {
        series: [
          {
            data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
      sparkline2.render();

      const option_sparkline3 = {
        series: [
          {
            data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
          },
        ],
        chart: {
          type: 'area',
          height: 50,
          sparkline: {
            enabled: true,
          },
        },
        stroke: {
          curve: 'straight',
        },
        fill: {
          opacity: 0.3,
        },
        yaxis: {
          min: 0,
        },
        colors: ['#DCE6EC'],
      };

      const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
      sparkline3.render();
    </script>
    <!--end::Script-->
