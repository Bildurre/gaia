// Block editor: SCF builds the block settings form before the sidebar is
// visible and its tabs start half initialized (no active tab and the fields
// of every tab shown together). When a tab group shows up with no active
// tab, its tabs are activated from the last to the first, which leaves the
// first one open and the fields of the others hidden.
const TAB_REPAIR_DELAY = 150;

function repairTabGroup(tabWrap) {
  const tabLinks = [...tabWrap.querySelectorAll('.acf-tab-group > li > a')];

  if (!tabLinks.length || tabWrap.querySelector('.acf-tab-group > li.active') || !tabWrap.offsetParent) {
    return;
  }

  tabLinks.reverse().forEach((tabLink) => {
    // The links have an empty href: never let one navigate
    tabLink.addEventListener('click', (clickEvent) => clickEvent.preventDefault(), { once: true });
    tabLink.click();
  });
}

function initBlockEditorTabsRepair() {
  let repairTimeout = null;

  const scheduleRepair = () => {
    window.clearTimeout(repairTimeout);
    repairTimeout = window.setTimeout(() => {
      document.querySelectorAll('.acf-tab-wrap').forEach(repairTabGroup);
    }, TAB_REPAIR_DELAY);
  };

  new MutationObserver(scheduleRepair).observe(document.body, { childList: true, subtree: true });
  scheduleRepair();
}

initBlockEditorTabsRepair();
