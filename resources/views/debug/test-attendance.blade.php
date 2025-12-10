<!DOCTYPE html>
<html>
<head>
    <title>Test Attendance</title>
</head>
<body>
    <h1>Test Attendance Form</h1>
    <form action="{{ route('attendance.process-scan', $qrCode->code) }}" method="POST">
        @csrf
        <input type="hidden" name="latitude" value="-6.200000">
        <input type="hidden" name="longitude" value="106.816666">
        <input type="hidden" name="accuracy" value="10">
        
        <button type="submit">Test Submit</button>
    </form>
</body>
</html>