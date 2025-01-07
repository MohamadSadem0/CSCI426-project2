import { initializeCommentHandlers } from './modules/comments/index.js';
import { initializeFollowHandlers } from './modules/followHandler.js';
import { initializeLikeHandlers } from './modules/likeHandler.js';
import { initializeModalHandlers } from './modules/modalHandler.js';
import { initializePostHandlers } from './modules/postHandler.js';
import { initializeUserStatsHandlers } from './modules/userStatsHandler.js';

document.addEventListener('DOMContentLoaded', () => {
  initializeLikeHandlers();
  initializeCommentHandlers();
  initializeFollowHandlers();
  initializePostHandlers();
  initializeUserStatsHandlers();
  initializeModalHandlers();
});
