export const ARRAY_UNIQUE = (value, index, self) => {
	return self.indexOf(value) === index;
};

export const insert = (arr, index, items) => [
	...arr.slice(0, index),
	...items,
	...arr.slice(index),
];
