workflow "Inspections" {
  on = "pull_request"
  resolves = ["Run PHPCS inspection"]
}

action "Run PHPCS inspection" {
  uses = "rtCamp/action-phpcs-code-review@master"
  secrets = ["VAULT_ADDR", "VAULT_TOKEN"]
  args = ["WordPress,WordPress-Core,WordPress-Docs"]
}
