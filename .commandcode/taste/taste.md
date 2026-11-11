# Taste

## Workflow

- Wants a plan presented and approved before starting a non-trivial refactor or architecture change ("please share the plan before starting refactor"). Confidence: 0.8

## Design

- Challenges design choices that add extra state/schema (e.g., version columns) and pushes back asking why existing data relationships couldn't achieve the same guarantee; favors the minimal mechanism. Confidence: 0.65
- Prefers reusing conventions/mechanisms the framework already provides (e.g., Sanctum's native token format) or existing app building blocks (e.g., an existing model/endpoint) instead of inventing a custom scheme; will ask for existing pieces to be leveraged "maybe reusing … if possible". Confidence: 0.7
