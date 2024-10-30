/**
 * Event to clear current selection in the calendar.
 */
export const CLEAR_SELECTION_EVENT = 'bw_clear_selection';
const clearSelectionEvent = new Event(CLEAR_SELECTION_EVENT);
export const clearSelection = () => document.dispatchEvent(clearSelectionEvent);
