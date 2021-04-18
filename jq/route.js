var tab = [{"p":"1","c":"2"},{"p":"1","c":"3"},{"p":"2","c":"4"},{"p":"3","c":"5"},{"p":"5","c":"6"},{"p":"5","c":"7"},{"p":"5","c":"8"},{"p":"8","c":"9"}];
function group(arr) {
  var tree = {};
  for(var i in arr) {
  	if (!tree[arr[i].p]) {
  		tree[arr[i].p] = [arr[i].c];

  	} else {
  		tree[arr[i].p].push(arr[i].c);
  	}
  }
  return tree;
}
var routes = [];
function route_from(arr, key, route=[]) {
	route.push(key);
	if (!arr[key]) {
		routes.push(route);
		return [];
	}

	var val = arr[key];
	for(var i in val) {
		var nroute = route.filter(() => true);
		route_from(arr, val[i], nroute);
	}
}

var group = group(tab);
route_from(group, '1');
console.log(routes);
