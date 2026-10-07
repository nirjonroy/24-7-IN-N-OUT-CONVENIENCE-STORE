(() => {
  const explorer = document.querySelector('[data-repair-explorer]');
  if (!explorer) return;

  const brand = explorer.querySelector('[data-repair-brand]');
  const device = explorer.querySelector('[data-repair-device]');
  const service = explorer.querySelector('[data-repair-service]');
  const status = explorer.querySelector('[data-repair-status]');
  const result = explorer.querySelector('[data-repair-result]');
  const modelsUrl = explorer.dataset.modelsUrl;
  const estimateUrl = explorer.dataset.estimateUrl;

  const setStatus = message => {
    if (status) status.textContent = message;
  };

  const resetResult = message => {
    if (result) result.innerHTML = `<p>${message}</p>`;
  };

  const setDeviceOptions = (options, placeholder) => {
    device.innerHTML = `<option value="">${placeholder}</option>`;
    options.forEach(option => {
      const element = document.createElement('option');
      element.value = option.id;
      element.textContent = option.model_number ? `${option.name} (${option.model_number})` : option.name;
      device.appendChild(element);
    });
  };

  const disableDevice = message => {
    setDeviceOptions([], message);
    device.disabled = true;
  };

  const disableService = message => {
    service.value = '';
    service.disabled = true;
    service.options[0].textContent = message;
  };

  const enableService = () => {
    service.disabled = false;
    service.options[0].textContent = 'Choose a repair service';
  };

  const loadModels = async () => {
    disableDevice('Loading devices...');
    disableService('Choose a device first');
    resetResult('Select a device and repair service to see public estimate information.');
    setStatus('Loading devices...');

    if (!brand.value) {
      disableDevice('Choose a brand first');
      setStatus('Start by selecting a brand.');
      return;
    }

    try {
      const url = new URL(modelsUrl, window.location.origin);
      url.searchParams.set('brand_id', brand.value);
      const response = await fetch(url, { headers: { Accept: 'application/json' } });
      if (!response.ok) throw new Error('Unable to load devices.');
      const payload = await response.json();
      const models = payload.data || [];
      setDeviceOptions(models, models.length ? 'Choose a device' : 'No supported devices listed');
      device.disabled = models.length === 0;
      setStatus(models.length ? 'Select a device.' : 'No supported devices are currently listed for this brand.');
    } catch (error) {
      disableDevice('Unable to load devices');
      setStatus('Unable to load repair information right now. Please contact the store.');
    }
  };

  const loadEstimate = async () => {
    resetResult('Checking repair options...');
    setStatus('Checking repair options...');

    if (!device.value || !service.value) {
      resetResult('Select a device and repair service to see public estimate information.');
      setStatus(device.value ? 'Select a repair service.' : 'Select a device.');
      return;
    }

    try {
      const url = new URL(estimateUrl, window.location.origin);
      url.searchParams.set('device_model_id', device.value);
      url.searchParams.set('repair_service_id', service.value);
      const response = await fetch(url, { headers: { Accept: 'application/json' } });
      if (!response.ok) throw new Error('Unable to load estimate.');
      const data = await response.json();
      const rows = [
        ['Brand', data.brand],
        ['Device', data.model_number ? `${data.device} (${data.model_number})` : data.device],
        ['Repair', data.service],
        ['Availability', data.availability_label],
        ['Price', data.formatted_price || data.price_label],
        ['Estimated time', data.estimated_range || (data.estimated_minutes ? `About ${data.estimated_minutes} minutes` : null)],
        ['Warranty', data.warranty],
        ['Note', data.note],
      ].filter(row => row[1]);

      result.innerHTML = rows.map(row => `<p><span class="font-extrabold text-slate-950 dark:text-white">${row[0]}:</span> ${row[1]}</p>`).join('');
      setStatus(data.available ? 'Repair information loaded.' : 'This repair is currently unavailable for the selected device.');
    } catch (error) {
      resetResult('Unable to load repair information right now. Please contact the store.');
      setStatus('Unable to load repair information right now. Please contact the store.');
    }
  };

  brand.addEventListener('change', loadModels);
  device.addEventListener('change', () => {
    service.value = '';
    resetResult('Select a repair service to see public estimate information.');
    if (device.value) {
      enableService();
      setStatus('Select a repair service.');
    } else {
      disableService('Choose a device first');
      setStatus('Select a device.');
    }
  });
  service.addEventListener('change', loadEstimate);
})();
