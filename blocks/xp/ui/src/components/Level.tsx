import React, { forwardRef } from "react";
import { useString } from "../lib/hooks";

import { Level } from "../lib/types";

type Props = { level: Level; small?: boolean; medium?: boolean };

const Level = forwardRef<HTMLDivElement, Props>(({ level, small, medium }, ref) => {
  const label = useString("levelx", "block_xp", level.level);
  const classes = "block_xp-level level-" + level.level + (small ? " small" : medium ? " medium" : "");

  if (level.badgeurl) {
    return (
      <div className={classes + " level-badge"} aria-label={label} ref={ref}>
        <img src={level.badgeurl} alt={label} />
      </div>
    );
  }

  return (
    <div className={classes} aria-label={label} ref={ref}>
      {level.level}
    </div>
  );
});

export default Level;
