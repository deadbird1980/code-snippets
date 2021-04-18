var tab = [{"p":"1","c":"2"},{"p":"1","c":"3"},{"p":"2","c":"4"},{"p":"3","c":"5"},{"p":"5","c":"6"},{"p":"5","c":"7"},{"p":"5","c":"8"},{"p":"8","c":"9"}];
function route(arr) {
  var rtn=[], r = [], fnd = false;
  for(var i in arr) {
  	console.log('i=', i);
  	for(var j in rtn) {
  		var indx = rtn[j].indexOf(arr[i].p);
  		console.log('index=', indx);
  		if (indx >= 0) {
  			if (indx + 1 == rtn[j].length) {
  			  rtn[j].push(arr[i].c);
  			} else {
  			  r = rtn[j].slice(0, indx-2);
  			}
  			fnd = true;
  			
  		} else {
  			fnd = false;
  			r = [arr[i].p, arr[i].c];
  		}
  	}
  	if (fnd) {
        r.push(arr[i].p);
        r.push(arr[i].c);
  	} else {
  		r.push(arr[i].p);
        r.push(arr[i].c);
  	}
  	
  	rtn.push(r);
  }
  console.log('rtn=', rtn);
}

route(tab);