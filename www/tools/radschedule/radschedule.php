<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<?php $title = "Radiator Schedule"; ?>

<div class="container mt-3" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <button class="btn btn-warning float-end" @click="resetStorage" v-if="radiators.length">Clear</button>
            <h3>Radiator Schedule</h3>
            <p>Calculate heat output from a list of radiators</p>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="flow_temperature">Flow temperature</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" v-model.number="flow_temperature" @change="update">
                    <span class="input-group-text">°C</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label for="return_DT">DT</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" v-model.number="return_DT" @change="update">
                    <span class="input-group-text">°K</span>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label for="room_temperature">Room temperature</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" v-model.number="room_temperature" @change="update">
                    <span class="input-group-text">°C</span>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col">
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Length</th>
                        <th>Height</th>
                        <th>Rated Output</th>
                        <th>Heat Output</th>
                        <th>Oversize Factor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(radiator, index) in radiators" :key="index">

                        <td>{{ radiator.type }}</td>
                        <td>{{ radiator.length }}</td>
                        <td>{{ radiator.height }}</td>
                        <td>{{ radiator.rated_output }} W</td>
                        <td>{{ radiator.heat_output }} W</td>
                        <td>{{ radiator.oversize_factor }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeRadiator(index)">Remove</button>
                        </td>
                    </tr>
                </tbody>

                <!-- Totals -->
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th></th>
                        <th></th>
                        <th>{{ total_rated_output }} W</th>
                        <th>{{ total_heat_output }} W</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group">
                <div class="input-group mb-3">

                    <!-- Select radiator type -->
                    <span class="input-group-text">Type</span>
                    <select class="form-select" v-model="add_type">
                        <option v-for="type in radiator_types" :value="type">{{ type }}</option>
                    </select>

                    <!-- Select radiator length -->
                    <span class="input-group-text">Length</span>
                    <input type="text" class="form-control" v-model.number="add_length" @change="validate_rad">

                    <!-- Select radiator height -->
                    <span class="input-group-text">Height</span>
                    <input type="text" class="form-control" v-model.number="add_height" @change="validate_rad">

                    <button class="btn btn-primary" @click="addRadiator">Add radiator</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /*
    Radiator equations

    K1:
        300: m = 0.517
        450: m = 0.768
        600: m = 1.000
        700: m = 1.142

    continuous:
        m = -0.000000454*height^2 + 0.002017178*height - 0.047435696
    
    P+
        300: m = 0.776
        450: m = 1.105
        600: m = 1.409
        700: m = 1.597

    continuous:
        m = -0.000000567*height^2 + 0.002620013*height + 0.040997375

    K2:
        300: m = 1.012
        450: m = 1.409
        600: m = 1.778
        700: m = 2.011
    
    continuous:
        m = -0.00000058*height^2 + 0.00308043*height + 0.14058005

    K3:
        300: m = 1.418
        500: m = 2.168
        600: m = 2.514
        700: m = 2.841

    continuous:
        m = -0.000000961*height^2 + 0.004518773*height + 0.1489

    */

    var app = new Vue({
        el: '#app',
        data: {
            radiator_types: ["K1","P+","K2","K3"],

            flow_temperature: 40,
            return_DT: 5,
            room_temperature: 20,

            radiators: [],

            total_heat_output: 0,
            total_rated_output: 0,

            // Add radiator
            add_type: "K2",
            add_height: 600,
            add_length: 1200
        },
        methods: {
            update: function () {
                this.model();
            },
            model: function() {
                var total_heat_output = 0;
                var total_rated_output = 0;

                // Loop through radiators
                for (var i = 0; i < this.radiators.length; i++) {
                    var radiator = this.radiators[i];

                    // Calculate mean water temperature and MWT - Room DT
                    var return_temperature = this.flow_temperature - this.return_DT;
                    var MWT = (this.flow_temperature + return_temperature) / 2;
                    var DT = MWT - this.room_temperature;

                    // Radiator heat output equation
                    radiator.heat_output = Math.round(radiator.rated_output * Math.pow(DT / radiator.rated_DT, 1.3));

                    // Add to totals
                    total_heat_output += radiator.heat_output;
                    total_rated_output += radiator.rated_output;

                    // Oversize factor
                    radiator.oversize_factor = (radiator.rated_output / radiator.heat_output).toFixed(2);
                }

                // Update totals
                this.total_heat_output = total_heat_output;
                this.total_rated_output = total_rated_output;
            },

            validate_rad: function() {
                // Limit height and length
                if (this.add_height < 300) this.add_height = 300;
                if (this.add_height > 700) this.add_height = 700;
                if (this.add_length < 400) this.add_length = 400;
                if (this.add_length > 3000) this.add_length = 3000;

                // Round to nearest
                this.add_height = Math.round(this.add_height / 10) * 10;
                this.add_length = Math.round(this.add_length / 100) * 100;
            },

            addRadiator: function() {

                this.add_height = parseInt(this.add_height);
                this.add_length = parseInt(this.add_length);

                var m = 0;
                var output = 0;

                if (this.add_type == "K1") {
                    m = -0.000000454*Math.pow(this.add_height,2)+0.002017178*this.add_height-0.047435696;
                    output = Math.round(m * this.add_length);
                }
                else if (this.add_type == "P+") {
                    m = -0.000000567*Math.pow(this.add_height,2)+0.002620013*this.add_height+0.040997375;
                    output = Math.round(m * this.add_length);
                }
                else if (this.add_type == "K2") {
                    m = -0.00000058*Math.pow(this.add_height,2)+0.00308043*this.add_height+0.14058005;
                }
                else if (this.add_type == "K3") {
                    m = -0.000000961*Math.pow(this.add_height,2)+0.004518773*this.add_height+0.1489;
                }

                output = Math.round(m * this.add_length);

                
                this.radiators.push({
                    type: this.add_type,
                    length: this.add_length,
                    height: this.add_height,
                    rated_output: output,
                    rated_DT: 50,
                    heat_output: 0,
                    oversize_factor: 0
                });

                // Save to local storage
                if (typeof(Storage) !== "undefined") {
                    localStorage.setItem('radiators', JSON.stringify(this.radiators));
                }

                this.model();
            },

            removeRadiator: function(index) {
                this.radiators.splice(index, 1);
                this.model();
            },

            resetStorage: function() {
                if (typeof(Storage) !== "undefined") {
                    localStorage.removeItem('radiators');
                }
                this.radiators = [];
                this.model();
            }
        }
    });

    // Get radiators from local storage if available
    if (typeof(Storage) !== "undefined") {
        var radiators = localStorage.getItem('radiators');
        if (radiators) {
            app.radiators = JSON.parse(radiators);
        } else {
            app.radiators = [];
            app.addRadiator();
        }
    }

    
    app.model();
</script>
