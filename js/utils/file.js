export const basename = str => {
   return new String(str).substring(str.lastIndexOf('/') + 1);
};

export const withoutPrefix = str => {
	return new String(str).substring(str.indexOf('-') + 1)
};
