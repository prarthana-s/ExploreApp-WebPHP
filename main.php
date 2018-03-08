<?php 

$nearbyPlacesJSON = '';

$key = YOUR_API_KEY;

if (isset($_POST)) {

    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody,true);

    // Check if the request is for showing reviews and photos
    if ($data['funcName'] == 'displayPlaceDetails') {

        // Call Google Places API to fetch place details
        $placeID = $data['placeID'];
        $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=$placeID&key=$key";
        $placeDetails = file_get_contents($url);
        $placeDetailsJSON = json_decode($placeDetails,true);

        // If photos exist, call Google Places API "Places Photos" to get max 5 high-res photos
        if(array_key_exists('photos', $placeDetailsJSON["result"])) {
            $photos = $placeDetailsJSON["result"]["photos"];
            $countPhotos = count($photos);
            if ($countPhotos > 5) {
                $countPhotos = 5;
            }
            
            // Fetch the photos and store them in a folder named "images" on the server
            // Name the photos sequentially as photo0, photo1 and so on with png extension
            $maxWidth = 750;
            for ($i = 0 ; $i < $countPhotos ; $i++) {
                $photoReference = $photos[$i]["photo_reference"];
                $imageURL = "https://maps.googleapis.com/maps/api/place/photo?maxwidth=$maxWidth&photoreference=$photoReference&key=$key";
                $image = file_get_contents($imageURL);
                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/images/photo'.$i.'.png', $image); 
            }
        }

        // If photos do not exist, put additional logic here
        // else {
        //     echo "No photos exist";
        // }

        echo $placeDetails;

        die();
    }
}

if(isset($_POST["submit"])) {

    // User has entered location
    // Fetch latitude and longitude
    if ($_POST['locationRadio'] == 'location') {
        $locationName = urlencode($_POST['locationInput']);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$locationName&key=$key";
        $locationDetails = file_get_contents($url);
        $locationDetailsJSON = json_decode($locationDetails,true);
        $lat = $locationDetailsJSON["results"][0]["geometry"]["location"]["lat"];
        $lon = $locationDetailsJSON["results"][0]["geometry"]["location"]["lng"];
    }

    // Use current location coordinates
    else {
        $lat = $_POST['hereLatitude'];
        $lon = $_POST['hereLongitude'];
    }

    // Miles to metres conversion
    $distance = $_POST['distance'];
    if ($distance == 0) {
        $distance = 10;
    }
    $radius = $distance * 1609.34;


    // Handle case where keyword consists of multiple words/special characters
    $keyword = urlencode($_POST['keyword']);
    $type = $_POST['category'];

    //If type is default, pass empty string as type parameter
    if ($type == 'default') {
        $type = "";
    }

    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$lat,$lon&radius=$radius&type=$type&keyword=$keyword&key=$key";
    $nearbyPlacesJSON = file_get_contents($url);

    $x = json_decode($nearbyPlacesJSON,true);
    $x['lat'] = $lat;
    $x['lon'] = $lon;
    $nearbyPlacesJSON = json_encode($x);
    // echo $nearbyPlacesJSON;
}

?>


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
            margin-left: 250px;
            margin-right: 250px;
        }

        #noRecordsFound {
            border: solid 1px grep;
            background-color: lightgrey;
            text-align: center;
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
        }

        table, td, th {
            border: 1px solid black;
            border-spacing: 0px;
        }

        .iconImg {
            width: 40px;
        }

        .photosBody {
            display: none;
        }

        .reviewsBody {
            display:none;
        }

        .map {
            height: 300px;
            z-index: 2;
            display: none;
            /* position: relative; */
        }

        .toolTip {
            height: 300px;
            width: 300px;
            z-index: 1;
            position: absolute;
            display: none;
        }

        .modesOfTravel {
            position: absolute;
            background-color: lightgrey;
            z-index: 3;
            display: none;
        }


        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .placeAddressLine {
            position: relative;
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
                <form id="mainForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">

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
        </div>


        <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>

        <script>

        // This is defined globally to fetch the current script
        // Used later to fetch images from server for "Photos" feature
        var script = document.currentScript;
        var fullUrl = script.src;

        var bodyElement = document.getElementsByTagName('body')[0];

        // Used for Reviews and Photos panel
        var arrowUpIcon = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
        var arrowDownIcon = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";

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

        // Handling of radio buttons
        var radioSelectionLoc = document.getElementById('locationRadioLoc');
        var radioSelectionHere = document.getElementById('locationRadioHere');        
        var textInput = document.getElementById("locationInputText");

        radioSelectionLoc.addEventListener('change',toggleRequired,false);
        radioSelectionHere.addEventListener('change',disableTextBox,false);


        function displayPlaceDetails(ev) {
            let target = event.target;

            // Check if target was indeed a place name
            // Required because event listener is on the entire table element
            // User could have also clicked on an address or icon
            if (target.className == 'placeName') {
                var xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var placeDetailsJSON = JSON.parse(xhr.responseText);
                        var placeDetailsResult = placeDetailsJSON["result"];

                        var arrowWidth = 30;

                        var panelName = document.createElement('div');
                        var panelNameHTML = '<div id="panelName">' + placeDetailsResult["name"] + '</div>';
                        panelName.innerHTML = panelNameHTML;

                        var reviewsPanel = document.createElement('div');
                        var reviewsPanelHTML = '<div id="reviewsPanel">Click here to view reviews</div><div class="arrow"><img class="toggleReviewsArrow" width="' + arrowWidth + '" src="' + arrowDownIcon + '"/></div>';
                        reviewsPanel.innerHTML = reviewsPanelHTML;
                        
                        var photosPanel = document.createElement('div');
                        var photosPanelHTML = '<div id="photosPanel">Click here to view photos</div><div class="arrow"><img class="togglePhotosArrow" width="' + arrowWidth + '" src="' + arrowDownIcon + '"/></div>';
                        photosPanel.innerHTML = photosPanelHTML;
                
                        var photosBody = document.createElement('div');
                        photosBody.className = "photosBody";
 
                        var reviewsBody = document.createElement('div');
                        reviewsBody.className = "reviewsBody";

                        // Display reviews
                        if ('reviews' in placeDetailsResult) {
                            var reviews = placeDetailsJSON["result"]["reviews"];
                            reviewsHTML = '<table id="reviewsTable">';
                            for (let i = 0 ; i < reviews.length ; i++) {
                                let authorName = reviews[i]["author_name"];
                                let userPhotoURL = reviews[i]["profile_photo_url"];
                                let reviewText = reviews[i]["text"];

                                reviewsHTML += '<tr><td><img class="userPhoto" src="' + userPhotoURL + '" alt="user image"/>' + authorName + '</tr><tr><td>' + reviewText + '</td></tr>';
                            }
                            reviewsHTML += "</table>";
                            reviewsBody.innerHTML = reviewsHTML;
                        }
                        else {
                            reviewsBody.innerHTML = 'No Reviews Found';
                        }
                        reviewsPanel.appendChild(reviewsBody);

                        // Display photos
                        if ('photos' in placeDetailsResult) {
                            let photosLen = placeDetailsResult["photos"].length;
                            photosHTML = '<table id="photosTable">';
                            photosLen > 5 ? photosLen = 5 : photosLen = photosLen ;
                            
                            for (let i = 0 ; i < photosLen ; i++) {
                                photosHTML += '<a target="_blank" href="' + fullUrl + '/images/photo' + i + '.png"><img src="' + fullUrl + '/images/photo' + i + '.png"/></a>';
                            }
                            photosHTML += "</table>";
                            photosBody.innerHTML = photosHTML;
                        }
                        else {                            
                            photosBody.innerHTML = 'No Photos Found';
                        }
                        photosPanel.appendChild(photosBody);


                        var placesTable = document.getElementById('placesTable');
                        placesTable.parentNode.removeChild(placesTable);

                        bodyElement.appendChild(panelName);

                        bodyElement.appendChild(reviewsPanel);
                        bodyElement.appendChild(photosPanel);

                        var toggleReviewsArrow = document.getElementsByClassName('toggleReviewsArrow')[0];
                        var togglePhotosArrow = document.getElementsByClassName('togglePhotosArrow')[0];

                        toggleReviewsArrow.addEventListener('click', toggleReviewsFunc);
                        togglePhotosArrow.addEventListener('click', togglePhotosFunc);
                    }
                };
                xhr.open('POST', 'main.php');
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify({
                    funcName: 'displayPlaceDetails', 
                    placeID : target.dataset.placeid
                }));
            }
            else if (target.className == 'placeAddressLine') {
                var map;

                locationCoordinates = {lat: parseFloat(target.parentNode.dataset.lat), lng: parseFloat(target.parentNode.dataset.lng)};

                var mapID = 'map' + target.parentNode.dataset.placeid;
                map = new google.maps.Map(document.getElementById(mapID), {
                    center: locationCoordinates,
                    zoom: 18
                });
                var marker = new google.maps.Marker({
                    position: locationCoordinates,
                    map: map
                });

                var modesOfTravelID = 'modesOfTravel' + target.parentNode.dataset.placeid;
                var thisModesOfTravel = document.getElementById(modesOfTravelID);
                thisModesOfTravel.style.display = 'block';

                var thisMap = document.getElementById(mapID);
                thisMap.style.display = 'block';

                var toolTipID = 'toolTip' + target.parentNode.dataset.placeid;
                var toolTip = document.getElementById(toolTipID);
                toolTip.style.display = 'block';
            } 
            else if (target.parentNode.className == 'modesOfTravel'){
                var lat = target.parentNode.parentNode.parentNode.dataset.lat;
                var lng = target.parentNode.parentNode.parentNode.dataset.lng;
                
                var tableElem = document.getElementById('placesTable');
                var myLat = tableElem.dataset.mylat;
                var myLon = tableElem.dataset.mylon;

                var directionsService = new google.maps.DirectionsService();
                var directionsDisplay = new google.maps.DirectionsRenderer();
                var originCoords = new google.maps.LatLng(myLat,myLon);
                var destCoords = new google.maps.LatLng(lat,lng);
                var mapOptions = {
                    zoom: 14,
                    center: destCoords
                }

                var mapID = 'map' + target.parentNode.parentNode.parentNode.dataset.placeid;
                map = new google.maps.Map(document.getElementById(mapID), {
                    center: locationCoordinates,
                    zoom: 18
                });

                var map = new google.maps.Map(document.getElementById(mapID), mapOptions);
                directionsDisplay.setMap(map);

                var request = {
                    origin: originCoords,
                    destination: destCoords,
                    travelMode: target.className.toUpperCase()
                };
                directionsService.route(request, function(response, status) {
                    if (status == 'OK') {
                    directionsDisplay.setDirections(response);
                    }
                });
 
            }
        }

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

        // Handles display of reviews panel
        // If reviews panel is shown, hide the photos panel
        function toggleReviewsFunc() {
            var reviewsPanel = document.getElementById('reviewsPanel');
            var photosPanel = document.getElementById('photosPanel');

            var reviewsBody = document.getElementsByClassName('reviewsBody')[0];
            var photosBody = document.getElementsByClassName('photosBody')[0];

            var reviewsArrow = document.getElementsByClassName('toggleReviewsArrow')[0];
            var photosArrow = document.getElementsByClassName('togglePhotosArrow')[0];

            if (reviewsBody.style.display == 'block') {
                reviewsBody.style.display = 'none';
                reviewsPanel.innerHTML = 'Click here to show reviews.'
                reviewsArrow.src = arrowDownIcon;
            }
            else {
                reviewsBody.style.display = 'block';   
                reviewsPanel.innerHTML = 'Click here to hide reviews.'
                reviewsArrow.src = arrowUpIcon;      

                photosBody.style.display = 'none';
                photosPanel.innerHTML = 'Click here to show photos.'   
                photosArrow.src = arrowDownIcon;                             
            }
        }

        // Handles display of photos panel
        // If photos panel is shown, hide the reviews panel
        function togglePhotosFunc() {
            var reviewsPanel = document.getElementById('reviewsPanel');
            var photosPanel = document.getElementById('photosPanel');
            
            var reviewsBody = document.getElementsByClassName('reviewsBody')[0];
            var photosBody = document.getElementsByClassName('photosBody')[0];
            
            var reviewsArrow = document.getElementsByClassName('toggleReviewsArrow')[0];
            var photosArrow = document.getElementsByClassName('togglePhotosArrow')[0];
            
            if (photosBody.style.display == 'block') {
                photosBody.style.display = 'none';
                photosPanel.innerHTML = 'Click here to show photos.'; 
                photosArrow.src = arrowDownIcon;                  
            }
            else {
                photosBody.style.display = 'block'; 
                photosPanel.innerHTML = 'Click here to hide photos.';  
                photosArrow.src = arrowUpIcon; 

                reviewsBody.style.display = 'none';               
                reviewsPanel.innerHTML = 'Click here to show reviews.';
                reviewsArrow.src = arrowDownIcon;                                                                                
            }
        }

        function generateHTML(address, placeID, lat, lng) {
            addrHTML = '';
            addrHTML += '<div data-lat="' + lat + '" data-lng="' + lng + '" data-placeID="' + placeID + '" class="placeAddress"><div class="placeAddressLine">' + address + 
            '</div><div class="toolTip" id="toolTip' + placeID + '"><div class="modesOfTravel" id="modesOfTravel' + placeID + '"><div class="walking">Walk there</div><div class="bicycling">Bike there</div><div class="driving">Drive there</div></div> \
            <div class="map" id="map' + placeID + '"></div></div> \
            </div>';
            return addrHTML;
        }

        // AJAX call to PHP script to fetch nearby places JSON data
        var xhttp2 = new XMLHttpRequest();
        var url = "main.php";

        // Process the nearby places JSON data and display in tabular form
        xhttp2.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                jsonObj = <?php echo json_encode($nearbyPlacesJSON); ?>;

                if (jsonObj) {
                    jsonObj = JSON.parse(jsonObj);
                    
                    var results = jsonObj.results;
                    var myLat = jsonObj.lat;
                    var myLon = jsonObj.lon;

                    if (!results.length) {
                        tableHTML = '<div id="noRecordsFound"><p>No records have been found.</p></div';
                    }

                    else {
                        tableHTML = '<table id="placesTable" data-myLat="' + myLat + '" data-myLon="' + myLon + '"><tr><th>Icon</th><th>Name</th><th>Address</th></tr>';

                        for (let i=0; i<results.length; i++) {
                            var icon = results[i].icon;
                            var name = results[i].name;
                            var address = results[i].vicinity;
                            var placeID = results[i].place_id;
                            var lat = results[i].geometry.location.lat;
                            var lng = results[i].geometry.location.lng;

                            tableHTML += '<tr><td><img class="placeIcon" src="' + icon + '" alt="user image"/></td><td class="placeName" data-placeid="' + placeID + '">' + name + '</td><td class="addressInfo">' + generateHTML(address,placeID,lat,lng) + '</td></tr>';
                        }
                        tableHTML += '</table>';
                    }

                    var userNameSpan = document.createElement('span');
                    userNameSpan.innerHTML = tableHTML;
                    bodyElement.appendChild(userNameSpan);
                    
                    var placesTable = document.getElementById('placesTable');
                    placesTable.addEventListener('click',displayPlaceDetails,false);

                }
            }
        };

        xhttp2.open("GET",url, true);
        xhttp2.send();

    </script>

    </body>

</html>