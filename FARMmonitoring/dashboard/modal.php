<!-- New Rate Modal -->
<div id="AddDeviceModal" class="modal">
  <div class="modal-content modal-sm">
    <div class="modal-header">Adding New Device</div>
    <div class="modal-body">
      <div class="device-location">
        <form id="NEWdeviceFORM" enctype="multipart/form-data">
          <div>
            <label for="sensor_type">Sensor type:</label>
            <select id="sensor_type" name="sensor_type" onchange="inputIDtype()">
              <option value="">--Select Type--</option>
              <option value="sensor_env_soil">Environment & Soil</option>
              <option value="sensor_water">Water</option>
            </select>
          </div>
          
          <div class="mt-10">
            <label for="sensor_id">Sensor ID:</label>
            <input id="sensor_id" name="sensor_id" type="text" placeholder="Sensor ID">
          </div>
        </form>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-color-white" onclick="closeAddDeviceModal();">Cancel</button>
      <button class="btn-color-blue" onclick="confirmAddDeviceModal()">Confirm</button>
    </div>
  </div>
</div>

