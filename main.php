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
                        <option value="beautySalon">beauty salon</option>
                        <option value="casino">casino</option>
                        <option value="movieTheater">movie theatre</option>
                        <option value="lodging">lodging</option>
                        <option value="airport">airport</option>
                        <option value="trainStation">train station</option>
                        <option value="subwayStation">subway station</option>
                        <option value="busStation">bus station</option>
                    </select>
                </div>

                <div class="distanceLocationElement">
                    <label for="distance">Distance (miles):</label> 
                    <input type="text" name="distance" placeholder="10" value="">            

                    <span class="locationElement">
                    <label for="locationRadio">from</label>

                    <input type="radio" id="locationRadioHere" name="locationRadio" value="here" required>
                    <label for="locationRadioHere">Here</label>
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


        <?php if(isset($_POST["submit"])): ?> 
            You have submitted the form! <?php echo $_POST["distance"]; ?>
        <?php endif; ?>

        <script>

        window.onload = function() {

            var xhttp = new XMLHttpRequest();
            var url = "http://ip-api.com/json";

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    jsonObj = JSON.parse(xhttp.responseText);
                    var searchButton = document.getElementById('searchButton');
                    searchButton.removeAttribute('disabled'); 
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

        function toggleRequired() {

            if (textInput.hasAttribute('required') !== true) {
                textInput.removeAttribute('disabled');
                textInput.setAttribute('required','required');
            }

            else {
                textInput.removeAttribute('required');  
            }
        }

        // Disable location text input if user selects "user"
        function disableTextBox() {
            if (radioSelectionHere.checked) {
                textInput.setAttribute('disabled','disabled');
            }
        }

        function resetForm() {
            document.getElementById("mainForm").reset();
        }

    </script>

    </body>

</html>