<!DOCTYPE html>
<html>
<head>
	<title>test</title>
</head>
<body>
<script type="text/javascript">
	function hapus(input)
	{
	 var a = document.getElementById(input);
	 a.value = '';
	 console.log(a);
	 
	}

	function clearBox(elementID)
{
    document.getElementById(elementID).innerHTML = "";
}
</script>

<input type="input" name="input" id="input">
<input type="submit" name="submit" onclick="hapus('input')">
</body>
</html>