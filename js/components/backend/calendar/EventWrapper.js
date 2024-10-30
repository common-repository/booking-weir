import {
	Component,
} from 'react';

import PropTypes from 'prop-types';

import {accessor as get} from 'react-big-calendar/lib/utils/accessors';
import {DnDContext} from 'react-big-calendar/lib/addons/dragAndDrop/DnDContext';

import EventSegment from 'components/backend/calendar/EventSegment';

/**
 * Draggable fork of `react-big-calendar` event wrapper that renders `EventSegment`.
 */
export default class EventWrapper extends Component {
	static contextType = DnDContext;

	static propTypes = {
		type: PropTypes.oneOf(['date', 'time']),
		event: PropTypes.object.isRequired,
		draggable: PropTypes.bool,
		allDay: PropTypes.bool,
		isRow: PropTypes.bool,
		continuesPrior: PropTypes.bool,
		continuesAfter: PropTypes.bool,
		isDragging: PropTypes.bool,
		isResizing: PropTypes.bool,
		resizable: PropTypes.bool,
	};

	handleResizeUp = e => {
		if(e.button !== 0) {
			return;
		}
		this.context.draggable.onBeginAction(this.props.event, 'resize', 'UP');
	}

	handleResizeDown = e => {
		if(e.button !== 0) {
			return;
		}
		this.context.draggable.onBeginAction(this.props.event, 'resize', 'DOWN');
	}

	handleResizeLeft = e => {
		if(e.button !== 0) {
			return;
		}
		this.context.draggable.onBeginAction(this.props.event, 'resize', 'LEFT');
	}

	handleResizeRight = e => {
		if(e.button !== 0) {
			return;
		}
		this.context.draggable.onBeginAction(this.props.event, 'resize', 'RIGHT');
	}

	handleStartDragging = e => {
		if(e.button !== 0) {
			return;
		}
		// hack: because of the way the anchors are arranged in the DOM, resize
		// anchor events will bubble up to the move anchor listener. Don't start
		// move operations when we're on a resize anchor.
		const isResizeHandle = e.target.className.includes('rbc-addons-dnd-resize')
		if(!isResizeHandle) {
			this.context.draggable.onBeginAction(this.props.event, 'move')
		}
	}

	renderAnchor(direction) {
		const cls = direction === 'Up' || direction === 'Down' ? 'ns' : 'ew'
		return (
			<div
				className={`rbc-addons-dnd-resize-${cls}-anchor`}
				onMouseDown={this[`handleResize${direction}`]}
			>
				<div className={`rbc-addons-dnd-resize-${cls}-icon`} />
			</div>
		);
	}

	render() {
		const {
			event,
			type,
			continuesPrior,
			continuesAfter,
			resizable,
		} = this.props;

		const {draggable} = this.context;
		const {draggableAccessor, resizableAccessor} = draggable;
		const isDraggable = draggableAccessor ? !!get(event, draggableAccessor) : true;
		const isResizable = resizable && (resizableAccessor ? !!get(event, resizableAccessor) : true);

		let StartAnchor = null;
		let EndAnchor = null;
		let dragProps = {};

		if(isResizable || isDraggable) {
			dragProps = {
				onMouseDown: this.handleStartDragging,
				onTouchStart: this.handleStartDragging,
			};

			if(isResizable) {
				if(type === 'date') {
					StartAnchor = !continuesPrior && this.renderAnchor('Left');
					EndAnchor = !continuesAfter && this.renderAnchor('Right');
				} else {
					StartAnchor = !continuesPrior && this.renderAnchor('Up');
					EndAnchor = !continuesAfter && this.renderAnchor('Down');
				}
			}
		}

		return (
			<EventSegment
				{...this.props}
				isDraggable={isResizable || isDraggable}
				isDragged={draggable.dragAndDropAction.interacting && draggable.dragAndDropAction.event.id === event.id}
				dragProps={dragProps}
				dragStartAnchor={StartAnchor}
				dragEndAnchor={EndAnchor}
			/>
		);
	}
}
