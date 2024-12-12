<script>
// Function to update content
function updateCardsContent(contentArray) {
  const container = document.querySelector('.content2-container');
  container.innerHTML = ''; // Clear current cards

  contentArray.forEach(item => {
    const cardHTML = `
      <div class="content2-card">
        <div class="content2-card-num">${item.value}</div>
        <div class="content2-card-text">
          <div class="content2-card-text-up">${item.unit}</div>
          <div class="content2-card-text-down">${item.label}</div>
        </div>
      </div>`;
    container.insertAdjacentHTML('beforeend', cardHTML);
  });
}

function updateCards() {
  const activeSiteLocation = document.querySelector('.site-location.active');
  const activeSensor = activeSiteLocation.getAttribute('data-region');
  $.ajax({
    url: 'fetch_cards.php', // Path to your PHP script
    method: 'GET',
    dataType: 'json',
    data: { activeSensor: activeSensor },
    success: function(response) {
      console.log(response);
      
      var activeDiv = document.querySelector('.nav-lists.nav-active');
      var activeDivText = activeDiv.textContent || activeDiv.innerText;
      console.log(activeDivText);

      // Check if response is empty or undefined
      if (!response || response.length === 0) {
        response = [{}]; // Default to an empty object if no data
      }

      if (activeDivText === "Environment & Soil") {
        const responseArray = [
          { id: 'temperature', value: response[0].temperature || '--', unit: '°C', label: 'Temperature' },
          { id: 'humidity', value: response[0].humidity || '--', unit: '%', label: 'Humidity' },
          { id: 'heat_index', value: response[0].heat_index || '--', unit: '°C', label: 'Heat Index' },
          { id: 'soil_moisture', value: response[0].soil_moisture || '--', unit: '%', label: 'Soil Moisture' },
          { id: 'soil_temp', value: response[0].soil_temp || '--', unit: '°C', label: 'Soil Temperature' }
        ];

        const nitrogen_unit = document.getElementById('nitrogen_unit');
        const phosphorus_unit = document.getElementById('phosphorus_unit');
        const potassium_unit = document.getElementById('potassium_unit');
        const ph_level_unit = document.getElementById('ph_level_unit');
        const nitrogen_label = document.getElementById('nitrogen_label');
        const phosphorus_label = document.getElementById('phosphorus_label');
        const potassium_label = document.getElementById('potassium_label');
        const ph_level_label = document.getElementById('ph_level_label');
        nitrogen_unit.innerHTML = "mg/kg";
        phosphorus_unit.innerHTML = "mg/kg";
        potassium_unit.innerHTML = "mg/kg";
        ph_level_unit.innerHTML = "";
        nitrogen_label.innerHTML = "Nitrogen";
        phosphorus_label.innerHTML = "Phosporus";
        potassium_label.innerHTML = "Potassium";
        ph_level_label.innerHTML = "pH Level";

        const nitrogen = document.getElementById('nitrogen');
        const phosphorus = document.getElementById('phosphorus');
        const potassium = document.getElementById('potassium');
        const ph_level = document.getElementById('ph_level');
        nitrogen.innerHTML = response[0].nitrogen;
        phosphorus.innerHTML = response[0].phosphorus;
        potassium.innerHTML = response[0].potassium;
        ph_level.innerHTML = response[0].ph_level;

        updateCardsContent(responseArray);
      } else if (activeDivText === "Water") {
        const responseArray = [
          { id: 'ph_level', value: response[0].ph_level || '--', unit: 'ph range', label: 'PH Level' },
          { id: 'tds', value: response[0].tds || '--', unit: 'PPM', label: 'TDS' },
          { id: 'water_level', value: response[0].water_level || '--', unit: 'm', label: 'Water Level' },
          { id: 'water_temp', value: response[0].water_temp || '--', unit: '°C', label: 'Water Temperature' },
          { id: '', value: '--' || '--', unit: '', label: '' }
        ];

        const elements = [
          'nitrogen', 'phosphorus', 'potassium', 'ph_level',
          'nitrogen_unit', 'phosphorus_unit', 'potassium_unit', 'ph_level_unit',
          'nitrogen_label', 'phosphorus_label', 'potassium_label', 'ph_level_label'
        ];

        elements.forEach(id => {
          const element = document.getElementById(id);
          element.innerHTML = id.includes('unit') || id.includes('label') ? "" : "--";
        });

        updateCardsContent(responseArray);
      }
      
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error:', status, error);
    }
  });
}

</script>