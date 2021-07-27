/**
 * Strip leading/trailing quotes from a string
 */
export function stripQuotes(value: string | null): string | null {
	if (value == null) {
		return null;
	}

	const trimmed = value.trim();

	if ((trimmed.startsWith(`'`) && trimmed.endsWith(`'`)) || (trimmed.startsWith('"') && trimmed.endsWith('"'))) {
		return trimmed.slice(1, -1);
	}

	return value;
}
