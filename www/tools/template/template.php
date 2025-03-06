<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<?php $title = "Template"; ?>

<div class="container mt-3" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <h3>Template</h3>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col">
            <label class="form-label">Flow temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="flow_temperature" @change="update">
                <span class="input-group-text">°C</span>
            </div>  
        </div>
        <div class="col">
            <label class="form-label">Return temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="return_temperature" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Flow rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="flow_rate" @change="update">
                <span class="input-group-text">L/min</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat output</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="heat" disabled>
                <span class="input-group-text">kW</span>
            </div>
        </div>
    </div>
</div>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            flow_temperature: 40,
            return_temperature: 35,
            specific_heat_capacity: 4.2, 
            flow_rate: 12, 
            heat: 0
        },
        methods: {
            update: function () {
                this.model();
            },
            model: function() {
                var DT = this.flow_temperature - this.return_temperature;
                var flow_rate = this.flow_rate / 60;
                this.heat = DT * flow_rate * this.specific_heat_capacity;
            }
        }
    });
    app.model();
</script>
