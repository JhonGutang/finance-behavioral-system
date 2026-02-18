import { describe, it, expect, beforeEach, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useBotanicalFeedback } from "../useBotanicalFeedback";
import { useToastStore } from "@/stores/toastStore";

describe("useBotanicalFeedback", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("triggers notifySeedSown with correct botanical theme", () => {
    const store = useToastStore();
    const { notifySeedSown } = useBotanicalFeedback();
    const addToastSpy = vi.spyOn(store, "addToast");

    notifySeedSown("Groceries");

    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "success",
        title: "Seed Sown",
        message: expect.stringContaining("planted in Groceries"),
      }),
    );
  });

  it("triggers notifySeedRefined with correct botanical theme", () => {
    const store = useToastStore();
    const { notifySeedRefined } = useBotanicalFeedback();
    const addToastSpy = vi.spyOn(store, "addToast");

    notifySeedRefined("Dining");

    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "success",
        title: "Seed Refined",
        message: expect.stringContaining("carefully adjusted"),
      }),
    );
  });

  it("triggers notifySeedUprooted with correct botanical theme", () => {
    const store = useToastStore();
    const { notifySeedUprooted } = useBotanicalFeedback();
    const addToastSpy = vi.spyOn(store, "addToast");

    notifySeedUprooted();

    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "warning",
        title: "Seed Uprooted",
        message: expect.stringContaining("removed from the garden ledger"),
      }),
    );
  });

  it("triggers notifyHarvestIntegration with correct counts", () => {
    const store = useToastStore();
    const { notifyHarvestIntegration } = useBotanicalFeedback();
    const addToastSpy = vi.spyOn(store, "addToast");

    notifyHarvestIntegration(10, 2);

    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "success",
        title: "Harvest Integration",
        message: expect.stringContaining("10 new seeds"),
      }),
    );
    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        message: expect.stringContaining("2 duplicates were pruned"),
      }),
    );
  });

  it("triggers notifyWisdomRefreshed with info theme", () => {
    const store = useToastStore();
    const { notifyWisdomRefreshed } = useBotanicalFeedback();
    const addToastSpy = vi.spyOn(store, "addToast");

    notifyWisdomRefreshed();

    expect(addToastSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: "info",
        title: "Wisdom Refreshed",
        message: expect.stringContaining(
          "behavioral engine has completed its analysis",
        ),
      }),
    );
  });
});
