<!DOCTYPE html>
<html>
<head>
<link href="<?echo base_url();?>css/template.css" rel="stylesheet" type="text/css"/>
<link href="<?echo base_url();?>css/board.css" rel="stylesheet" type="text/css"/>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="<?= base_url() ?>js/jquery.timers.js"></script>

<script>
//Board JS functions
	//7*6 game board
	    var board =[]
	    for (var i=0;i<6;i++){
		    board[i] = []
		    for (var j=0;j<7;j++){
			    board[i][j] = "";
			}
		}

		function fit(move){
			for (var str in move){ //str looks like xiyj
				var i = str[3]
				var j = str[1]
				board[i][j] = move[str]
			}
		}

		function checkCol(col,user){
			var num = 0;
			for (var i=0;i<6;i++){
				if (board[i][col] == user){
					num++;
				}else if (num > 0){
					break;
				}
			}
			if (num >= 4){
				return true;
			}else{
				return false;
			}
		}

		function checkRow(row,user){
			var num = 0;
			for (var j=0;j<7;j++){
				if (board[row][j] == user){
					num++;
				}else if (num > 0){
					break;
				}
			}
			if (num >= 4){
				return true;
			}else{
				return false;
			}
		}

		function checkDig(row,col,user){
			var num1=0;
			var num2=0;
			var num3=0;
			var num4=0;
           //from south to east
			var i1=row+1; 
			var j1=col-1;
		
			if (row==0 || col==0){
				i1 = row;
				j1 = col;
			}
			while(i1<6 && j1<7){
				if (board[i1][j1]==user)
					num1++;
				else if (num1>0)
					break;
				i1++;
				j1++;
			}
			//from south to west
			var i2 = row-1;
			var j2 = col+1;
			if (row == 0 || col == 6){
				i2 =row;
				j2 = col;
			}
			while (i2<6 &&j2 >= 0){
				if (board[i2][j2]==user)
					num2++;
				else if (num2>0)
					break;
				i2++;
				j2--;
			}
			//from north to east
			var i3=row+1;
			var j3=col-1;
			
			if (row==5 || col==0){
				i3 = row;
				j3 = col;
			}
			while(i3>=0 && j<7){
				if (board[i3][j3]==user)
					num3++;
				else if (num3>0)
					break;
				i3--;
				j3++;
			}

			//from north to west
			var i4=row+1;
			var j4=col+1;
			// If the cell is at the rightmost column and/or the bottommost row, there
			// isn't a southeast neighbour. So start counting from the cell itself.
			if (row==5 || col==6){
				i4 = row;
				j4 = col;
			}
			while(i4>=0 && j4>=0){
				if (board[i4][j4]==user)
					num4++;
				else if (num4>0)
					break;
				i4--;
				j4--;
			}

			var check = ((num1 >=4) || (num2 >=4) || (num2 >=4) ||(num2 >=4));
			return check;
			
			
		}

		function checkresult(row,col,user,move){
			fit(move);
			return ( checkDig(row,col,user) || checkRow(row,user) || checkCol(col,user));
		}

var otherUser = "<?= $otherUser->login ?>";
var user = "<?= $user->login ?>";
var status = "<?= $status ?>";
var piece1 = "<img src='<?= base_url() ?>images/black.png'>";
var piece2 = "<img src='<?= base_url() ?>images/red.png'>";
$(function(){
	
	$('body').everyTime(2000,function(){
		if (status == 'waiting') {
			$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
				if (data && data.status=='rejected') {
					alert("Sorry, your invitation to play was declined!");
					window.location.href = '<?= base_url() ?>arcade/index';
				}
				if (data && data.status=='accepted') {
					status = 'playing';
					$('#status').html('Your opponent: ' + otherUser);
					
					var url = "<?= base_url() ?>board/BoardSet";
					$.post(url,'turn='+user);
				}
					
			});
		}
		
		//function to chat from start code
		var url = "<?= base_url() ?>board/getMsg";
		$.getJSON(url, function (data,text,jqXHR){
			if (data && data.status=='success') {
				var conversation = $("[name='conversation']").val();
				var msg = data.message;
				if (msg.length > 0) 
					$("[name='conversation']").val(conversation+"\n"+otherUser+": " +msg);
					
				}
			
		});
	});
	
	
	$('form').submit(function(){
		var arguments = $(this).serialize();
		var url = "<?= base_url() ?>board/postMsg";
		$.post(url,arguments, function (data,textStatus,jqXHR){
			var conversation = $("[name='conversation']").val();
			var msg = $('[name=msg]').val();
			
			$("[name='conversation']").val(conversation+"\n"+user+": "+msg);
			
			$("input[name='msg']").val('');
			
			$("[name='conversation']").scrollTop($("[name='conversation']")[0].scrollHeight);
		});
		return false;
	});

    
	$('#turn').everyTime(20,'timer',function(){
		
		var url = "<?= base_url() ?>board/BoardState";
		$.getJSON(url, function (data,text,jqXHR){
			if (data && data.status=='success') {
				
				var turn = data.turn;
				
				if (turn==user) {
					$('#turn').html("IT IS YOUR TURN!");
					$('#turn').css({"background-color":"yellow","font-size":"200%"});
				}
				if (turn==otherUser) {
					$('#turn').html("Competitor's turn, please wait...");
					$('#turn').css({"background-color":"yellow","font-size":"200%"});
				
				}
				
				var move = JSON.parse(data.move);
				
				for (var str in move) {
					$("td[id="+str+"]").val(move[str]);
					
					if (move[str]==user)
						$("td[id="+str+"]").html(piece1); 
					else 
						$("td[id="+str+"]").html(piece2);
				}
				
				var result = data.result;
				
				if (result){ 
					$('#turn').stopTime('timer');
					if (result==user){
						$('#result').html("YOU WIN!!!");
					    $('#result').css({"background-color":"yellow","font-size":"200%"});
					}
					else if (result==otherUser){
						$('#result').html("You lose");
					 $('#result').css({"background-color":"blue","font-size":"200%"});
					}
					else if (result=="tie"){
						
						$('#result').html("Draw");
						 $('#result').css({"background-color":"green","font-size":"200%"});
					}
					$('#turn').html("");
					alert($('#result').html());
				} 
			}
		});
	});

	
	$('td').click(function(){
		if ($('#turn').html()!="IT IS YOUR TURN!" 
			|| $('#result').html()=="YOU WIN!!!" 
			|| $('#result').html()=="You lose")
			return;		
		
		var pos= $(this).attr('id');
		
		var x = pos[1];
		var y = pos[3];
		var i = 0;
		for (i=0; i<5; i++){
			if ($("#x"+x+'y'+(i+1)).val()!='')
				break;
		}
		
		$("#x"+x+'y'+i).val(user);
		$("#x"+x+'y'+i).html(piece1);
		
		var move = {};
		var nummove = 0;
		
		$("td").each(function(){
			if ($(this).val()!="") {
				nummove++;
				move[$(this).attr('id')] = $(this).val();
			}
		});
		
		var result = "";
		if (checkresult(parseInt(i),parseInt(x),user,move)){
			result = user;
		} else if (nummove==42){
			result = "tie";
		}
		
		var url = "<?= base_url() ?>board/BoardSet";
		var tmp = JSON.stringify(move);
		var arg = {"turn":otherUser, "move":tmp, "result":result};
		$.post(url,arg);
	});
});


</script>
</head> 
<body>  
<h1>Game Area</h1>
<div>
Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)')?>
</div>

<div id='status'> 
</div>
<p> Your Piece:         <img height="30" width="30" src="<?= base_url() ?>images/black.png"/><p>
<p> Competitor's Piece: <img height="30" width="30" src="<?= base_url() ?>images/red.png"/>
</p>
<br>
<span id='turn'></span><br>
<br>
<?php 
	if ($status == "playing")
		echo "Competitor's name: " . $otherUser->login;
	else
		echo "Wating for " . $otherUser->login;
//draw game board
   echo "<table>\n";
   echo "<tr><td>";
   echo "<br>\n<table id='game'>\n";
	for ($i=0;$i<6;$i++){
		echo "<tr>";
		for ($j=0;$j<7;$j++){
			echo "<td id='x{$j}y{$i}' value=''></td>";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "<p align='center'><span id='result'></span></p>";
	echo "</td><td>";
	
	echo "</td></tr></table>";

	echo "<br>";

    
    echo "<p> Chat with your Competitor </p>";
    echo form_textarea('conversation');
	echo form_open();
	echo form_input('msg');
	echo form_submit('Send','Send');
	echo form_close();
	echo anchor("arcade",'(Back to game lobby)');
	
	
	

	
	
?>
</body>
</html>
