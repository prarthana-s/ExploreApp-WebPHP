<!DOCTYPE HTML>
<html>
    <head>
    
    <style>
        #heading {
            font-style: italic;
        }
    </style>
    
    
    </head>

    <body>
        <form method="post">
            <h1 id="heading">Travel and Entertainment Search</h1>

            <label for="keyword">Keyword:</label> 
            <input type="text" name="keyword" value="">

            <label for="category">Category:</label> 
            <select name="category">
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

            <label for="distance">Distance (miles):</label> 
            <input type="text" name="distance" value="">            

            <label for="locationRadio">from</label>

            <input type="radio" id="locationRadioHere" name="locationRadio" value="here">
            <label for="locationRadioHere">Here</label>

            <input type="radio" id="locationRadioLoc" name="locationRadio" value="location">
            <input type="text" name="locationInput" value="">                        

            <button type="submit" value="search">Search</button>
            <button type="reset" value="clear">Clear</button>

        </form>
    
    </body>

</html>