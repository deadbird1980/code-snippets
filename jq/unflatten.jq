group_by(.parent)|[.[]|{key:.[0].parent,value:map(.child)}]|from_entries
