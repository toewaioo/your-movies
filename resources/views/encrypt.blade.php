<!DOCTYPE html>
<html>
<head>
    <title>Encrypt Streaming URL</title>
</head>
<body>

<h2>Secure Streaming URL Generator</h2>

<form method="POST" action="/encrypt">
    @csrf
    <input type="text" name="url" placeholder="Enter Real Video URL" 
           style="width:400px" required>

    <button type="submit">Encrypt</button>
</form>

@if(isset($secureUrl))
    <h3>Your Secure URL:</h3>
    <input type="text" value="{{ $secureUrl }}" style="width:500px">

    <h3>Preview Player:</h3>
    <video width="600" controls>
        <source src="{{ $secureUrl }}" type="video/mp4">
    </video>
@endif

</body>
</html>
