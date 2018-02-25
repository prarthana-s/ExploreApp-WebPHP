<!DOCTYPE HTML>
<html>
    <head>
    
    <style>
        #heading {
            font-style: italic;
        }

        .mainContainer {
            text-align: center;
            border: solid 1px grey;
            background-color: lightgrey;
            padding-bottom: 20px;
        }

        .formContainer {
            display: inline-block;
            text-align:left;
        }

        #locationRadioLoc {
            margin-left: 326px;
        }

        .buttonElements {
            margin-left: 100px;
        }

        #lineBreak {
            align: center;
            font-color
        }

    </style>
    
    
    </head>

    <body>

        <div class="mainContainer">
            <div class="headingContainer">
                <h1 id="heading">Travel and Entertainment Search</h1>
            </div>

            <hr id="lineBreak">
            
            <div class="formContainer">
                <form method="post" id="mainForm">

                <div class="keywordElement">
                    <label for="keyword">Keyword:</label> 
                    <input type="text" name="keyword" value="" required>
                </div>

                <div class="categoryElement">
                    <label for="category">Category:</label> 
                    <select name="category">
                        <option value="default" selected>default</option>
                        <option value="cafe">cafe</option>
                        <option value="bakery">bakery</option>
                        <option value="restaurant">restaurant</option>
                        <option value="beauty_salon">beauty salon</option>
                        <option value="casino">casino</option>
                        <option value="movie_theater">movie theatre</option>
                        <option value="lodging">lodging</option>
                        <option value="airport">airport</option>
                        <option value="train_station">train station</option>
                        <option value="subway_station">subway station</option>
                        <option value="bus_station">bus station</option>
                    </select>
                </div>

                <div class="distanceLocationElement">
                    <label for="distance">Distance (miles):</label> 
                    <input type="text" name="distance" placeholder="10" value="">            

                    <span class="locationElement">
                    <label for="locationRadio">from</label>

                    <input type="radio" id="locationRadioHere" name="locationRadio" value="here" checked='checked' required>
                    <label for="locationRadioHere">Here</label>

                    <input type="hidden" id="hereLatitude" name="hereLatitude" value="">
                    <input type="hidden" id="hereLongitude" name="hereLongitude" value="">
                    <br>
                    <input type="radio" id="locationRadioLoc" name="locationRadio" value="location" required>
                    <input type="text" id="locationInputText" name="locationInput" placeholder="location" value="">                        
                </div>

                <div class="buttonElements">
                    <button type="submit" id="searchButton" name="submit" value="search" disabled>Search</button>
                    <button onclick="resetForm()" name="clear" value="clear">Clear</button>
                </div>
            </form>
        </div>


        <?php 

        $nearbyPlacesJSON = '';
        
        foreach ($_POST as $key => $value) {
            echo $key . ":" . $value . "<br/>";
        }

        if(isset($_POST["submit"])): 
            $lat = $_POST['hereLatitude'];
            $lon = $_POST['hereLongitude'];

            // Miles to metres conversion
            $distance = $_POST['distance'];
            if ($distance == 0) {
                $distance = 10;
            }
            $radius = $distance * 1609.34;

            $keyword = $_POST['keyword'];
            $type = $_POST['category'];

            //If type is default, pass empty string as type parameter
            if ($type == 'default') {
                $type = "";
            }

            $key = YOUR_API_KEY;

            $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$lat,$lon&radius=$radius&type=$type&keyword=$keyword&key=$key";
            $nearbyPlacesJSON = file_get_contents($url);
        endif; 
        
        ?>

        <script>

        // Enable Search button only after user's geolocation is fetched
        window.onload = function() {

            var xhttp = new XMLHttpRequest();
            var url = "http://ip-api.com/json";

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    jsonObj = JSON.parse(xhttp.responseText);
                    var searchButton = document.getElementById('searchButton');
                    searchButton.removeAttribute('disabled'); 
                    document.getElementById('hereLatitude').value = jsonObj.lat; 
                    document.getElementById('hereLongitude').value = jsonObj.lon;                    
                }
            };

            xhttp.open("GET",url, true);
            xhttp.send();
        }

        var radioSelectionLoc = document.getElementById('locationRadioLoc');
        var radioSelectionHere = document.getElementById('locationRadioHere');        
        var textInput = document.getElementById("locationInputText");

        radioSelectionLoc.addEventListener('change',toggleRequired,false);
        radioSelectionHere.addEventListener('change',disableTextBox,false);

        // If "Location" is selected, enable the text field and make it required
        function toggleRequired() {

            if (textInput.hasAttribute('required') !== true) {
                textInput.removeAttribute('disabled');
                textInput.setAttribute('required','required');
            }

            else {
                textInput.removeAttribute('required');  
            }
        }

        // Disable location text input if user selects "Here"
        function disableTextBox() {
            if (radioSelectionHere.checked) {
                textInput.setAttribute('disabled','disabled');
            }
        }

        function resetForm() {
            document.getElementById("mainForm").reset();
        }


        // AJAX call to PHP script to fetch nearby places JSON data
        var xhttp2 = new XMLHttpRequest();
        var url = "main.php";

        // Process the nearby places JSON data and display in tabular form
        xhttp2.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                jsonObj = <?php echo json_encode($nearbyPlacesJSON); ?>;
                console.log(jsonObj);
            }
        };

        xhttp2.open("GET",url, true);
        xhttp2.send();

    </script>

    </body>

</html>