import { describe, expect, it } from "vitest";
import { formatDate, formatPrice } from "../../helpers/index.js";

describe("helpers/index", () => {
    it("formats a number as BRL currency", () => {
        expect(formatPrice(10.5)).toBe("R$\u00A010,50");
    });

    it("returns zero currency when value is empty", () => {
        expect(formatPrice(null)).toBe("R$ 0,00");
        expect(formatPrice("")).toBe("R$ 0,00");
    });

    it("formats a valid date using the default pattern", () => {
        expect(formatDate("2026-04-08")).toBe("08/04/2026");
    });

    it("returns dash when date is invalid", () => {
        expect(formatDate("invalid-date")).toBe("-");
        expect(formatDate(null)).toBe("-");
    });
});
